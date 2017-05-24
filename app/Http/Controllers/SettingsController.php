<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Input;

class SettingsController extends Controller {
  private $project;
  private $settings;

  public function save(Request $request, $project) {
    $this->verify_project($project);
    $this->settings = json_decode($request->getContent());

    if ($this->settings->allowEditProjectKey) {
      $this->project->key = $this->settings->projectkey;
    }

    if ($this->settings->allowEditProjectSlug) {
      $this->edit_slug();
    }

    $this->project->name = $this->settings->displayname;
    $this->project->save();
    return 200;
  }

  private function verify_project($slug) {
    $project = \App\Project::where('slug', '=', $slug)->first();
    if ($project == null) {
      die('project_not_found');
    }
    $this->project = $project;
  }

  /*
    Update table names and check for duplicates
  */
  private function edit_slug() {
    $newslug = $this->settings->slug;
  }
}
