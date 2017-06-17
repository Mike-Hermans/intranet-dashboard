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
    $this->table = $this->project->id . '_plugins';

    $rows = \DB::table($this->table)
    ->where('timestamp', '=', \DB::table($this->table)->max('timestamp'))
    ->get();
    return $rows;
  }

  public function tables($slug, $table = false) {
    $this->verify_project($slug);
    $this->table = $this->project->id . '_db';
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
      $lines[] = array($item->timestamp * 1000, $item->size);
    }

    return json_encode($lines);
  }

  public function list_tables() {
    $top = false;
    if ( is_numeric(Input::get('top')) ) {
      $top = Input::get('top');
    }
    $rows = \DB::table($this->table)
    ->select('table')
    ->where('timestamp', '=', \DB::table($this->table)->max('timestamp'))
    ->when($top, function($query) use ($top) {
        return $query->orderBy('size', 'desc')->limit($top);
    })
    ->get();
    $tables = array();
    foreach ($rows as $row) {
      $tables[] = $row->table;
    }
    return json_encode($tables);
  }

  public function usage($slug, $type = false) {
    $this->verify_project($slug);
    $this->table = $this->project->id . '_usage';

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
      $lines[] = array($item->timestamp * 1000, $item->$type);
    }

    return json_encode($lines);
  }

  public function status($slug, $date = false) {
    $this->verify_project($slug);

    $wp = \DB::table('wp_versions')
    ->where('project_id', $this->project->id)
    ->orderBy('timestamp', 'desc')->limit(1)
    ->get(['version']);

    $status = \DB::table('statuses')
    ->where('project_id', $this->project->id)
    ->orderBy('timestamp', 'desc')->limit(1)
    ->get();

    if ( isset($status[0]) ) {
      $status[0]->wp = $wp[0]->version;
      $status[0]->up *= 1000;
      return json_encode($status[0]);
    }

    return 0;
  }

  public function events($slug) {
    $this->verify_project($slug);
    $events = \DB::table('events')
    ->select('timestamp', 'event')
    ->where('project_id', $this->project->id)
    ->latest('timestamp')->limit(8)
    ->get()
    ->toArray();

    return $events;
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
      $query->where('timestamp', '>=', strtotime('7 days ago'));
    }
    return $query;
  }

  public function data_cleanup() {
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

  public function get_notes($slug) {
    $this->verify_project($slug);
    echo json_encode(
      $this->project->notes()
      ->get(['timestamp', 'note'])
      ->toArray()
    );
  }

  public function save_notes(Request $request, $slug) {
    $this->verify_project($slug);
    $note = new \App\Note;

    $content = json_decode($request->getContent(), true);

    $note->note = $content['note'];
    $note->timestamp = isset($content['timestamp']) ? $content['timestamp'] / 1000 : 0;
    $this->project->notes()->save($note);
    echo "200";
  }
}
