<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Input;

/**
 * Class DataController
 *
 * This class manages the retrieving of data for projects:
 * - Plugin information
 * - Table information
 * - Usage information
 * - Last events
 * - Getting saved notes
 *
 * @package App\Http\Controllers
 */
class DataController extends Controller
{
    private $table;

    /**
     * Returns the current plugins and their status for a project
     *
     * @param  string $slug Project slug
     * @return mixed
     */
    public function plugins($slug)
    {
        $this->verifyProject($slug);
        $this->table = $this->project->id . '_plugins';

        $rows = \DB::table($this->table)
        ->where('timestamp', '=', \DB::table($this->table)->max('timestamp'))
        ->get();
        return $rows;
    }

    /**
     * Returns data for a table, when no table is selected, returns data for all tables.
     *
     * @param  string $slug  Project slug
     * @param  string $table Table to select data from
     * @return string
     */
    public function tables($slug, $table = '', $latest = false)
    {
        $this->verifyProject($slug);
        $this->table = $this->project->id . '_db';

        if ($table == '') {
            return $this->listTables();
        }

        $items = \DB::table($this->table)
        ->when(
            $table,
            function ($query) use ($table) {
                return $query->select('timestamp', 'size')->where('table', $table);
            }
        )
        ->where(
            function ($query) {
                $this->dateRange($query);
            }
        )
        ->get();

        $lines = array();
        foreach ($items as $item) {
            $lines[] = array($item->timestamp * 1000, $item->size);
        }

        return json_encode($lines);
    }

    /**
     * Returns a list of all table names ordered by size
     *
     * @return string
     */
    public function listTables()
    {
        $top = false;

        // Check for 'top' parameter
        if (is_numeric(Input::get('top'))) {
            $top = Input::get('top');
        }
        $rows = \DB::table($this->table)
        ->select('table')
        ->where('timestamp', '=', \DB::table($this->table)->max('timestamp'))
        ->when(
            $top,
            function ($query) use ($top) {
                return $query->orderBy('size', 'desc')->limit($top);
            }
        )
        ->get();
        $tables = array();
        foreach ($rows as $row) {
            $tables[] = $row->table;
        }
        return json_encode($tables);
    }

    /**
     * Returns the usage values for a project. When a type is given, returns only for the type.
     *
     * @param  string $slug Project slug
     * @param  string $type Type to return data for
     * @return string
     */
    public function usage($slug, $type = '')
    {
        $this->verifyProject($slug);
        $this->table = $this->project->id . '_usage';

        $items = \DB::table($this->table)
        ->when(
            !empty($type),
            function ($query) use ($type) {
                return $query->select('timestamp', $type);
            }
        )
        ->where(
            function ($query) {
                $this->dateRange($query);
            }
        )
        ->get();

        $lines = array();
        foreach ($items as $item) {
            $lines[] = array($item->timestamp * 1000, $item->$type);
        }

        return json_encode($lines);
    }

    /**
     * Returns the most recent status for a project.
     *
     * @param  string $slug Project slug
     * @return int|string
     */
    public function status($slug)
    {
        $this->verifyProject($slug);

        $wp = \DB::table('wp_versions')
        ->where('project_id', $this->project->id)
        ->orderBy('timestamp', 'desc')->limit(1)
        ->get(['version']);

        $status = \DB::table('statuses')
        ->where('project_id', $this->project->id)
        ->orderBy('timestamp', 'desc')->limit(1)
        ->get();

        // Check if a status is found
        if (isset($status[0])) {
            $status[0]->wp = $wp[0]->version;
            $status[0]->up *= 1000;
            return json_encode($status[0]);
        }

        return 0;
    }

    /**
     * Returns a list of events for the project.
     *
     * @param  string $slug Project slug
     * @return mixed
     */
    public function events($slug)
    {
        $this->verifyProject($slug);
        $events = \DB::table('events')
        ->select('timestamp', 'event')
        ->where('project_id', $this->project->id)
        ->latest('timestamp')->limit(8)
        ->get()
        ->toArray();

        return $events;
    }

    /**
     * Clears all data older than a week
     */
    public function dataCleanup()
    {
        $projects = \App\Project::all();

        $tables = ['usage', 'db'];
        foreach ($projects as $project) {
            foreach ($tables as $table) {
                \DB::table($project->id . '_' . $table)
                ->where('timestamp', '<', strtotime('7 days ago'))
                ->delete();
            }
        }
        echo "All data older than a week ago has been removed.";
    }

    /**
     * Returns all notes belonging to a project.
     *
     * @param  string $slug Project slug
     * @return string
     */
    public function getNotes($slug)
    {
        $this->verifyProject($slug);
        return $this->project->notes()
            ->get(['timestamp', 'note'])
            ->toArray();
    }

    /**
     * Returns the most recent usage values.
     *
     * @param  string $slug Project slug
     * @return string
     */
    public function lastUsage($slug)
    {
        $this->verifyProject($slug);

        $lastusage = \DB::table($this->project->id . '_usage')
                  ->orderBy('timestamp', 'latest')
                  ->first();
        return json_encode($lastusage);
    }

    public function latest($slug)
    {
        $this->verifyProject($slug);
        $update_status = false;
        $updatetime = $this->project->last_updated;

        // Get last usage
        $lastusage = \DB::table($this->project->id . '_usage')
            ->select('hdd', 'ram', 'cpu', 'page')
            ->orderBy('timestamp', 'latest')
            ->first();

        // Get other information (if updated in the last 60 seconds
        $rows = \DB::table($this->project->id . '_db')
            ->where('timestamp', '=', $updatetime)
            ->orderBy('size', 'desc')->limit(4)
            ->get();

        if (empty($rows->toArray())) {
            $tables = null;
        } else {
            $tables = array();
            foreach ($rows as $row) {
                $tables[$row->table] = $row->size;
            }
        }

        $plugins = \DB::table($this->project->id . '_plugins')
            ->where('timestamp', '=', $updatetime)
            ->first();

        $wp = \DB::table('wp_versions')
            ->select('version')
            ->where('project_id', $this->project->id)
            ->where('timestamp', '=', $updatetime)
            ->first();

        $status = \DB::table('statuses')
            ->where('project_id', $this->project->id)
            ->where('timestamp', '=', $updatetime)
            ->first();

        if ($plugins || $wp || $status) {
            $update_status = true;
        }

        return json_encode(array(
            'timestamp' => $updatetime * 1000,
            'usage' => $lastusage,
            'tables' => $tables,
            'update_status' => $update_status
        ));
    }
}
