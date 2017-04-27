<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

class ProjectController extends Controller {
  public function get_projects() {
    return \App\Project::select()->get();
  }

  public function get_project( $slug ) {
    $project = \App\Project::where('slug', '=', $slug)->first();
    if ( $project == null ) {
      return 'not_found';
    }
    $project['projectkey'] = $project->getKey();
    return $project->toArray();
  }

  public function add_project(Request $request) {
    $name = $request->input('name', '');
    if ( ! empty('name') ) {
      if ( $this->create_new_project( $request->input('name') ) ) {
        return str_slug( $request->input('name') );
      }
    }
    return 'error';
  }

  public function set_key(Request $request, $slug) {
    $project = \App\Project::where('slug', '=', $slug)->first();
    if ( $project == null ) {
      return 'not_found';
    }
    $key = $request->input('key', '');
    if ( mb_strlen($key) < 8 ) {
      return 'invalid_length';
    }
    $project->key = $key;
    return 'success';
  }

  private function create_new_project( $projectname ) {
    $project = new \App\Project;
    $project->name = $projectname;
    $project->slug = str_slug($projectname, '-');
    $project->key = '';
    $project->timestamps = false;
    $project->save();

    $this->generate_usage_table( $project->slug );
    $this->generate_db_table( $project->slug );

    return true;
  }

  private function generate_usage_table( $name ) {
    \Schema::create( $name . '_usage', function( Blueprint $table ) {
      $table->integer('timestamp');
      $table->float('hdd', 5, 2);
      $table->float('ram', 5, 2);
      $table->float('rx', 8, 2);
      $table->float('tx', 8, 2);
      $table->smallInteger('page');
    });
  }

  private function generate_db_table( $name ) {
    \Schema::create( $name . '_db', function( Blueprint $table ) {
      $table->integer('timestamp');
      $table->string('table', 64);
      $table->float('size', 8, 2);
    });
  }

  private function generate_plugin_table( $name ) {
    \Schema::create( $name . '_plugins', function( Blueprint $table ) {
      $table->integer('timestamp');
      $table->string('name', 64);
      $table->string('version', 16);
      $table->string('slug', 64);
      $table->boolean('active');
      $table->string('new_version', 16);
    });
  }
}
