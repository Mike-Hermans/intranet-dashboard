<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Input;

class DataController extends Controller {
  private $project;
  private $table;

  public function plugins($slug) {
    $this->verify_project($slug);
    $table = $slug . '_plugins';
    return $this->select($table);
  }

  public function tables($slug, $table = false) {
    $this->verify_project($slug);
    $this->table = $slug . '_db';
    if ( ! $table ) {
      return $this->list_tables();
    }

    $items = \DB::table($this->table)
    ->when($table, function($query) use ($table) {
        return $query->select('timestamp', 'size')->where('table', $table);
    })
    ->where(function($query) {
      $this->date_range($query);
    })
    ->get();

    $lines = array();
    foreach($items as $item) {
      $lines[] = array($item->timestamp, $item->size);
    }

    return json_encode($lines);
  }

  public function list_tables() {
    $rows = \DB::table($this->table)
    ->select('table')
    ->where('timestamp', '=', \DB::table($this->table)->max('timestamp'))
    ->get();
    $tables = array();
    foreach ($rows as $row) {
      $tables[] = $row->table;
    }
    return json_encode($tables);
  }

  public function usage($slug, $type = false) {
    \DB::enableQueryLog();
    $this->verify_project($slug);
    $this->table = $slug . '_usage';

    $items = \DB::table($this->table)
    ->when($type, function($query) use ($type) {
        return $query->select('timestamp', $type);
    })
    ->where(function($query) {
      $this->date_range($query);
    })
    ->get();

    $lines = array();
    foreach($items as $item) {
      $lines[] = array($item->timestamp, $item->$type);
    }

    return json_encode($lines);
  }

  private function select() {
    return \DB::table($this->table)->get();
  }

  private function verify_project( $slug ) {
    $project = \App\Project::where('slug', '=', $slug)->first();
    if ($project == null) {
      die('project_not_found');
    }
    $this->project = $project;
  }

  private function date_range($query) {
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

    if ($from) {
      $query->where('timestamp', '>=', $from);
      if ($to) {
        $query->where('timestamp', '<=', $to);
      }
    } else if ($on) {
      $query->where('timestamp', '=', $on);
    } else {
      // If no time is selected, use default
      $query->where('timestamp', '>=', strtotime('240 hours ago'));
    }
    return $query;
  }

}
