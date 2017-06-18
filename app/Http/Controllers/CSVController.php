<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Input;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class CSVController
 *
 * This class manages the generation of CSV's.
 *
 * @package App\Http\Controllers
 */
class CSVController extends Controller
{
    private $table;

    /**
     * Return the usage for a project in CSV format
     *
     * @param  string $slug Project slug
     * @return StreamedResponse
     */
    public function usage($slug)
    {
        $this->verifyProject($slug);
        $this->table = $this->project->id . '_usage';

        $items = \DB::table($this->table)
        ->latest('timestamp')
        ->when(
            is_numeric(Input::get('top')),
            function ($query) {
                return $query->limit(Input::get('top'));
            }
        )
        ->get()->toArray();
        $items = array_reverse($items);
        $columns = array('timestamp', 'hdd', 'ram', 'rx', 'tx', 'page', 'cpu');
        $response = new StreamedResponse(
            function () use ($items, $columns) {
                // Open output stream
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($items as $item) {
                     fputcsv(
                         $file,
                         array(
                         date('Y-m-d H:i:s', $item->timestamp),
                         $item->hdd,
                         $item->ram,
                         $item->rx,
                         $item->tx,
                         $item->page,
                         $item->cpu
                         )
                     );
                }
                fclose($file);
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment;filename=' . $slug . '-usage.csv;',
            ]
        );
        return $response;
    }

    /**
     * Create a CSV with usage data for all projects where all values exist.
     *
     * @return StreamedResponse
     */
    public function prepareMl()
    {
        $projects = \App\Project::all()->toArray();

        $columns = array('hdd', 'ram', 'page', 'cpu');

        // A StreamedResponse is used to prevent timeouts
        $response = new StreamedResponse(
            function () use ($projects, $columns) {
                // Open output stream
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($projects as $project) {
                    $items = \DB::table($project['id'] . '_usage')
                    ->select('hdd', 'ram', 'page', 'cpu')
                    ->whereNotNull('hdd')
                    ->whereNotNull('ram')
                    ->whereNotNull('page')
                    ->whereNotNull('cpu')
                    ->get()
                    ->toArray();

                    foreach ($items as $item) {
                         fputcsv(
                             $file,
                             array(
                             $item->hdd,
                             $item->ram,
                             $item->page,
                             $item->cpu
                             )
                         );
                    }
                }
                fclose($file);
            },
            200,
            [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment;filename=all-usage.csv;',
            ]
        );
        return $response;
    }
}
