<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CSVController extends Controller {
  private $project;
  private $table;

  public function usage($slug) {
    $this->verify_project($slug);
    $this->table = $slug . '_usage';

    $items = \DB::table($this->table)->get()->toArray();
    $columns = array('timestamp', 'hdd', 'ram', 'rx', 'tx', 'page');
    $response = new StreamedResponse( function() use ($items, $columns){
        // Open output stream
      $file = fopen('php://output', 'w');
      fputcsv($file, $columns);

      foreach($items as $item) {
         fputcsv($file, array($item->timestamp,$item->hdd,$item->ram,$item->rx,$item->tx,$item->page));
      }
      fclose($file);
    }, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment;filename=' . $slug . '-usage.csv;',
    ]);
    return $response;
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
