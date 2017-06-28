<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;

/**
 * Class FetchDataController
 *
 * This class manages the saving of data for a project.
 *
 * @package App\Http\Controllers
 */
class FetchDataController extends Controller
{
    private $timestamp;

    /**
     * Prepares a timestamp to be used for this dataset and calls other
     * functions related to saving the data.
     *
     * @param  Request $request contains all data for the project
     * @return string
     */
    public function collect(Request $request)
    {
        if (! $this->verifyHeader($request->header('Authorization'))) {
            return 'error';
        }
        // Set a single timestamp for all data currently being saved
        $this->timestamp = date(date_timestamp_get(date_create()));

        // Update the last_updated column
        $this->project->last_updated = $this->timestamp;
        $this->project->save();

        $this->saveData(json_decode($request->getContent(), true));

        $this->callForecasts();

        return 'success';
    }

    /**
     * Saves a note for a project. When a timestamp is given, saves
     * a note for the specific timestamp.
     *
     * @param  Request $request contains data for the note
     * @param  string  $slug    Project slug
     * @return int
     */
    public function saveNotes(Request $request, $slug)
    {
        $this->verifyProject($slug);

        $content = json_decode($request->getContent(), true);
        $timestamp = isset($content['timestamp']) ? $content['timestamp'] / 1000 : 0;

        $note = \App\Note::where('project_id', $this->project->id)->where('timestamp', $timestamp)->first();
        if (is_null($note)) {
            $note = new \App\Note;
            $note->timestamp = $timestamp;
        } else {
            if (empty($content['note'])) {
                $note->delete();
            }
        }
        $note->note = $content['note'];

        $this->project->notes()->save($note);
        return 200;
    }

    /**
     * Controls the Basic Auth header from the request. Returns
     * true when the credentials match.
     *
     * @param  string $authorization Authorization header parameter
     * @return bool
     */
    private function verifyHeader($authorization)
    {
        if ($authorization == null || empty($authorization)) {
            echo 'no_auth_header';
            return false;
        }

        $authorization = str_replace('Basic ', '', $authorization);
        $authorization = base64_decode($authorization);
        $project = explode(':', $authorization);
        if (count($project) != 2) {
            echo 'invalid_value';
            return false;
        }
        $slug = $project[0];
        $key = $project[1];
        $project = \App\Project::where('slug', '=', $slug)->first();
        if ($project == null) {
            echo 'project_not_found';
            return false;
        }
        if (! $project->matchKey($key)) {
            echo 'invalid_key';
            return false;
        }
        $this->project = $project;
        return true;
    }

    /**
     * Checks for existing data in the payload and calls
     * the corresponding data saving functions.
     *
     * @param object $content Project data object
     */
    private function saveData($content)
    {
        if (isset($content['status'])) {
            $currentstatus = $this->project->status()
                ->select('php', 'os', 'mem', 'disk', 'up')
                ->where('project_id', $this->project->id)
                ->orderBy('timestamp', 'desc')->first();
            $insertnewvalues = false;
            if ($currentstatus != null) {
                $currentstatus = $currentstatus->toArray();
                foreach ($currentstatus as $key => $value) {
                    if ($value != $content['status'][$key]) {
                        $insertnewvalues = true;
                    }
                }
            } else {
                $insertnewvalues = true;
            }

            if ($insertnewvalues) {
                $status = $content['status'];
                $status['timestamp'] = $this->timestamp;
                $status['project_id'] = $this->project->id;
                $this->project->status()->insert($status);
            }
        }

        /*
         * Update version only if it's different
         */
        if (isset($content['wp_version'])) {
            $currentversion = $this->project->WPVersion()
                ->select('version')
                ->where('project_id', $this->project->id)
                ->orderBy('timestamp', 'desc')->first();
            if ($currentversion == null || $currentversion->version != $content['wp_version']['wp']) {
                $wp_version = array();
                $wp_version['version'] = $content['wp_version']['wp'];
                $wp_version['timestamp'] = $this->timestamp;
                $wp_version['project_id'] = $this->project->id;
                $this->project->WPVersion()->insert($wp_version);
            }
        }

        if (isset($content['events']) && ! empty($content['events'])) {
            foreach ($content['events'] as $event) {
                $this->project->events()->insert(
                    array(
                    'project_id' => $this->project->id,
                    'timestamp' => $this->timestamp,
                    'event' => $event
                    )
                );
            }
        }

        if (isset($content['usage'])) {
            $this->saveUsageData($content['usage']);
        }

        if (isset($content['tables'])) {
            // Check if minutes equal to zero
            if (date('i', $this->timestamp) == '00') {
                $this->saveTableData($content['tables']);
            }
        }

        if (isset($content['plugins'])) {
            $this->savePluginData($content['plugins']);
        }
    }

    /**
     * Calls the forecast function for each value
     */
    private function callForecasts()
    {
        $fc = new ForecastController();

        $forecast_values = Settings::get($this->project->id . '_forecast_values');

        if ($forecast_values) {
            foreach ($forecast_values as $forecast_value) {
                $fc->forecast($this->project->slug, $forecast_value);
            }
        }
    }

    /**
     * Save data for tables
     *
     * @param array $tables
     */
    private function saveTableData($tables)
    {
        $dbtable = $this->project->id . '_db';
        foreach ($tables as $table => $size) {
            $row = array(
            'timestamp' => $this->timestamp,
            'table' => $table,
            'size' => $size
            );
            \DB::table($dbtable)->insert($row);
        }
    }

    /**
     * Save data for usage values
     *
     * @param object $usage
     */
    private function saveUsageData($usage)
    {
        $table = $this->project->id . '_usage';
        $usage['timestamp'] = $this->timestamp;
        $usage['page'] = $this->ping();
        \DB::table($table)->insert($usage);
    }

    /**
     * Save data for plugins
     *
     * @param object $plugins
     */
    private function savePluginData($plugins)
    {
        $dbtable = $this->project->id . '_plugins';
        foreach ($plugins as $plugin) {
            $plugin['timestamp'] = $this->timestamp;
            \DB::table($dbtable)->insert($plugin);
        }
    }

    /**
     * Check if a website can be reached. Returns value in seconds or -1
     * when the site can not be reached.
     *
     * @return int|null
     */
    private function ping()
    {
        if ($this->project->url) {
            $ch = curl_init($this->project->url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if (curl_exec($ch)) {
                $info = curl_getinfo($ch);
                curl_close($ch);
                return $info['total_time'];
            }

            curl_close($ch);
            return -1;
        }
        return null;
    }
}
