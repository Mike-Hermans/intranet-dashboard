<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Input;

class DataController extends Controller {
  private $project;

  public function plugins($slug) {
    $this->verify_project($slug);
    $table = $slug . '_plugins';
    return $this->select($table);
  }

  public function tables($slug) {
    $this->verify_project($slug);
    $table = $slug . '_tables';
    return $this->select($table);
  }

  public function usage($slug, $type = false) {
    \DB::enableQueryLog();
    $this->verify_project($slug);
    $table = $slug . '_usage';

    $from = false;
    $to = false;
    $on = false;

    if ( is_numeric(Input::get('from')) ) {
      $from = Input::get('from');
      if ( is_numeric(Input::get('to')) ) {
        $to = Input::get('to');
      }
    } else if ( is_numeric(Input::get('on')) ) {
      $on = Input::get('on');
    }
    $all = true;
    $items = \DB::table($table)
    ->when($type, function($query) use ($type) {
        return $query->select('timestamp', $type);
    })
    ->where(function($query) use ($type, $from, $to, $on) {
      if ($from) {
        $query->where('timestamp', '>=', $from);
        if ($to) {
          $query->where('timestamp', '<=', $to);
        }
      } else if ($on) {
        $query->where('timestamp', '=', $on);
      } else {
        // If no time is selected, use default
        $query->where('timestamp', '>=', strtotime('24 hours ago'));
      }
    })
    ->get();

    if ($type) {
      $lines = array();
      foreach($items as $item) {
        $lines[] = array($item->timestamp, $item->$type);
      }
    } else {
      $lines = $items;
    }

    return json_encode($lines);
  }

  private function select($table) {
    return \DB::table($table)->get();
  }

  private function verify_project( $slug ) {
    $project = \App\Project::where('slug', '=', $slug)->first();
    if ($project == null) {
      die('project_not_found');
    }
    $this->project = $project;
  }

}
