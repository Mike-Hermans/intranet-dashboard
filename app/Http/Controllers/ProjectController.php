<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

/*
  This controller manages the creating, updating and removing
  of projects.
*/
class ProjectController extends Controller {
  private $project;
  private $settings;

  /*
    Return all projects ordered alphabetically
  */
  public function get_projects() {
    return \App\Project::select()->orderBy('name')->get();
  }

  /*
    Get specific information for a project
  */
  public function get_project( $slug ) {
    $this->verify_project($slug);
    if ($this->project) {
      $this->project['projectkey'] = $this->project->getKey();
      return $this->project->toArray();
    }
    return 'not_found';
  }

  /*
    Update the project settings, return 200 if everything worked.
  */
  public function update(Request $request, $project) {
    $this->verify_project($project);
    $this->settings = json_decode($request->getContent());

    if ($this->settings->allowEditProjectKey) {
      $this->project->key = $this->settings->projectkey;
    }

    if ($this->settings->allowEditProjectSlug) {
      $this->project->slug = $this->settings->slug;
    }

    $this->project->url = $this->settings->url;
    $this->project->name = $this->settings->displayname;
    $this->project->save();
    return 200;
  }

  public function add(Request $request) {
    $name = $request->input('name', '');
    if ( ! empty('name') ) {
      if ( $this->create_new_project( $request->input('name') ) ) {
        return str_slug( $request->input('name') );
      }
    }
    return 'error';
  }

  private function create_new_project( $projectname ) {
    $project = new \App\Project;
    $project->name = $projectname;
    $project->slug = str_slug($projectname, '-');
    $project->key = '';
    $project->timestamps = false;
    $project->save();

    $this->generate_usage_table( $project->id );
    $this->generate_db_table( $project->id );
    $this->generate_plugin_table( $project->id );

    return true;
  }

  private function generate_usage_table( $id ) {
    \Schema::create( $id . '_usage', function( Blueprint $table ) {
      $table->integer('timestamp');
      $table->float('hdd', 5, 2);
      $table->float('ram', 5, 2);
      $table->float('cpu', 5, 2);
      $table->float('rx', 8, 2);
      $table->float('tx', 8, 2);
      $table->float('page', 9, 3);
    });
  }

  private function generate_db_table( $id ) {
    \Schema::create( $id . '_db', function( Blueprint $table ) {
      $table->integer('timestamp');
      $table->string('table', 64);
      $table->float('size', 8, 2);
    });
  }

  private function generate_plugin_table( $id ) {
    \Schema::create( $id . '_plugins', function( Blueprint $table ) {
      $table->integer('timestamp');
      $table->string('name', 64);
      $table->string('version', 16)->nullable();
      $table->string('slug', 64);
      $table->string('uri', 120);
      $table->boolean('active')->nullable();
      $table->string('new_version', 16)->nullable();
    });
  }

  /*
    Verify if a project exists
  */
  private function verify_project( $slug ) {
    $project = \App\Project::where('slug', '=', $slug)->first();
    if ($project == null) {
      $this->project = false;
    }
    $this->project = $project;
  }
}
