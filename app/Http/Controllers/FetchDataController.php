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
    $this->timestamp = date(date_timestamp_get(date_create()));
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
    $this->project = $project->slug;
    return 'true';
  }

  private function save_data( $content ) {
    $row = $content['usage'];
    $row['timestamp'] = $this->timestamp;
    $row['page'] = 500;
    $table = $this->project . '_usage';
    \DB::table($table)->insert($row);

    if (isset($content['tables'])) {
      $this->save_table_data($content['tables']);
    }

    if (isset($content['plugins'])) {
      $this->save_plugin_data($content['plugins']);
    }
  }

  private function save_table_data($tables) {
    $dbtable = $this->project . '_db';
    foreach($tables as $table => $size) {
      $row = array(
        'timestamp' => $this->timestamp,
        'table' => $table,
        'size' => $size
      );
      \DB::table($dbtable)->insert($row);
    }
  }
}
