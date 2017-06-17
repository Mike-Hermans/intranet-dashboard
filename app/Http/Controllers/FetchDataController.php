<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

class FetchDataController extends Controller {
  private $project;
  private $timestamp;

  public function collect(Request $request) {
    if ( ! $this->verify_header($request->header('Authorization') ) ) {
      return 'error';
    }
    // Set a single timestamp for all data currently being saved
    $this->timestamp = date(date_timestamp_get(date_create()));

    // Update the last_updated column
    $this->project->last_updated = $this->timestamp;
    $this->project->save();

    $this->save_data(json_decode($request->getContent(), true));
    return 'success';
  }

  private function verify_header( $authorization ) {
    if ( $authorization == null || empty($authorization)) {
      echo 'no_auth_header';
      return false;
    }

    $authorization = str_replace('Basic ', '', $authorization);
    $authorization = base64_decode($authorization);
    $project = explode( ':', $authorization );
    if ( count($project) != 2) {
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
    if ( ! $project->matchKey($key)) {
      echo 'invalid_key';
      return false;
    }
    $this->project = $project;
    return 'true';
  }

  private function save_data( $content ) {
    if(isset($content['status'])) {
      $currentstatus = $this->project->status()
      ->select('php', 'os', 'mem', 'disk', 'up')
      ->where('project_id', $this->project->id)
      ->orderBy('timestamp', 'desc')->first();
      $insertnewvalues = false;
      if ( $currentstatus != null ) {
        $currentstatus = $currentstatus->toArray();
        foreach ($currentstatus as $key => $value) {
          if ( $value != $content['status'][$key]) {
            $insertnewvalues = true;
          }
        }
      } else {
        $insertnewvalues = true;
      }

      if ( $insertnewvalues ) {
        $status = $content['status'];
        $status['timestamp'] = $this->timestamp;
        $status['project_id'] = $this->project->id;
        $this->project->status()->insert($status);
      }
    }

    /*
      Update version only if it's different
    */
    if(isset($content['wp_version'])) {
      $currentversion = $this->project->wp_version()
      ->select('version')
      ->where('project_id', $this->project->id)
      ->orderBy('timestamp', 'desc')->first();
      if ( $currentversion == null || $currentversion->version != $content['wp_version']['wp'] ) {
        $wp_version = array();
        $wp_version['version'] = $content['wp_version']['wp'];
        $wp_version['timestamp'] = $this->timestamp;
        $wp_version['project_id'] = $this->project->id;
        $this->project->wp_version()->insert($wp_version);
      }
    }

    if(isset($content['events']) && ! empty($content['events'])) {
      foreach($content['events'] as $event) {
        $this->project->events()->insert(array(
          'project_id' => $this->project->id,
          'timestamp' => $this->timestamp,
          'event' => $event
        ));
      }
    }

    if (isset($content['usage'])) {
      $this->save_usage_data($content['usage']);
    }

    if (isset($content['tables'])) {
      // Check if minutes equal to zero
      if (date('i', $this->timestamp) == '00') {
        $this->save_table_data($content['tables']);
      }
    }

    if (isset($content['plugins'])) {
      $this->save_plugin_data($content['plugins']);
    }
  }

  private function save_table_data($tables) {
    $dbtable = $this->project->id . '_db';
    foreach($tables as $table => $size) {
      $row = array(
        'timestamp' => $this->timestamp,
        'table' => $table,
        'size' => $size
      );
      \DB::table($dbtable)->insert($row);
    }
  }

  private function save_usage_data($usage) {
    $table = $this->project->id . '_usage';
    $usage['timestamp'] = $this->timestamp;
    $usage['page'] = $this->ping();
    \DB::table($table)->insert($usage);
  }

  private function save_plugin_data($plugins) {
    $dbtable = $this->project->id . '_plugins';
    foreach($plugins as $plugin) {
      $plugin['timestamp'] = $this->timestamp;
      \DB::table($dbtable)->insert($plugin);
    }
  }

  private function ping() {
    if ( $this->project->url ) {
      $ch = curl_init($this->project->url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
      if (curl_exec($ch)) {
        $info = curl_getinfo($ch);
        curl_close($ch);
        return $info['total_time'];
      }

      // If connection fails, return a -1
      curl_close($ch);
      return -1;
    }
    return null;
  }
}
