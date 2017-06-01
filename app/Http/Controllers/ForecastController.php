<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;

class ForecastController extends Controller {
  private $slug;
  private $project;
  private $type;
  private $message;
  private $last_point;
  private $csv_file;

  public function get_forecast($slug, $type) {
    $this->slug = $slug;
    if (!$this->verify_project()) {
      die('project_not_found');
    }

    $forecast = \App\Forecast::select('forecast')
    ->where('project_id', '=', $this->project['id'])
    ->where('type', '=', $type)
    ->first();

    echo $forecast['forecast'];
  }

  public function forecast($slug, $type) {
    $this->slug = $slug;
    $this->type = $type;

    if ( ! $this->can_forecast()) {
      die($this->message);
    }

    // At this moment we know the project exists, have it's ID and can forecast.
    $this->generate_csv();

    // Get the result for the forecast data
    $forecast = shell_exec('cd $(pwd)/r; Rscript forecast.R ' . $this->csv_file);

    // Remove the header
    $forecast = preg_replace('/^.+\n/', '', $forecast);

    // Split the output into lines
    $lines = explode("\n", $forecast);

    // Convert the values into an array
    $forecast = array();
    $point = $this->last_point;
    foreach ($lines as $line) {
      if (!empty($line)) {
        $values = preg_split('/\s+/', trim($line));
        $forecast[] = array(
          'point' => $point,
          'forecast' => $values[1],
          'lo80' => $values[2],
          'hi80' => $values[3],
          'lo95' => $values[4],
          'hi95' => $values[5]
        );
        // Add 10 minutes to the last point
        $point += 600;
      }
    }
    // Remove the generated CSV file
    shell_exec('rm $(pwd)/r/' . $this->csv_file);
    $this->save_forecast(json_encode($forecast, JSON_NUMERIC_CHECK));
  }

  private function save_forecast($forecast_values) {
    $forecast = \App\Forecast::select('id')
    ->where('project_id', '=', $this->project['id'])
    ->where('type', '=', $this->type)
    ->first();

    if ($forecast == null) {
      $forecast = new \App\Forecast();
      $forecast->project_id = $this->project['id'];
      $forecast->type = $this->type;
    }

    $forecast->last_point = $this->last_point;
    $forecast->forecast = $forecast_values;
    $forecast->save();
    echo('OK');
  }

  /*
    Check if project exists and allows forecasting
  */
  private function verify_project() {
    $project = \App\Project::select('id', 'slug')->where('slug', '=', $this->slug)->first();
    if ( $project == null ) {
      return false;
    }
    $this->project = $project;
    return true;
  }

  /*
    Generate a CSV containing every tenth value for the pas 5k values
  */
  private function generate_csv() {
    $type = $this->type;
    $items = \DB::table($this->project['id'] . '_usage')
    ->select('timestamp', $type)
    ->latest('timestamp')
    ->limit(2000)
    ->get()
    ->toArray();
    $this->last_point = $items[0]->timestamp;
    $items = array_reverse($items);
    $file = fopen('r/' . $this->csv_file, 'a+' );
    fputcsv( $file, array($type) );
    $count = 0;
    $total = 0;
    foreach ( $items as $item ) {
        $count++;
        $total += $item->type;
        if ($count == 10) {
          fputcsv( $file, array(number_format($total / 10, 2) ) );
          $total = 0;
          $count = 0;
        }
    }
    fclose( $file );
  }

  /*
    Check if we can forecast new data:
    - Project exists and allows forecasting
    - Forecast file does not (which means forecast is in progress)
    - Forecast does not exist OR is older than one hour
    - There are at least 5k values
  */
  private function can_forecast() {
    if ( !$this->verify_project()) {
      $this->message = 'project_not_found';
      return false;
    }
    $this->csv_file = $this->slug . '_' . $this->type . '.csv';
    // If file exists, we're already forecasting the data.
    if (file_exists('r/' . $this->csv_file)) {
      $this->message = 'forecast_in_progress';
      return false;
    }

    $count = \DB::table($this->project['id'] . '_usage')
    ->select('timestamp', $this->type)->count();

    if ($count < 2000) {
      $this->message = 'not_enough_values_' . $count;
      return false;
    }

    return true;
  }
}
