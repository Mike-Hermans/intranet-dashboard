<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Input;

/**
 * Class Controller
 *
 * Basic controller class that contains functions used by every other controller.
 *
 * @package App\Http\Controllers
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $project;

    /**
     * Verify if a project exists.
     *
     * @param string $slug Project slug
     * @return void
     */
    protected function verifyProject($slug)
    {
        $project = \App\Project::where('slug', '=', $slug)->first();
        if ($project == null) {
            die('project_not_found');
        }
        $this->project = $project;
    }

    /**
     * Used to select a date range for return values. When no value is given,
     * returns data for the last week.
     *
     * @param  object $query DB Query object
     * @return mixed
     */
    protected function dateRange($query)
    {
        $from = false;
        $to = false;
        $on = false;

        if (is_numeric(Input::get('from'))) {
            $from = Input::get('from');
            if (is_numeric(Input::get('to'))) {
                $to = Input::get('to');
            }
        } elseif (is_numeric(Input::get('on'))) {
            $on = Input::get('on');
        }

        if ($from) {
            $query->where('timestamp', '>=', $from);
            if ($to) {
                $query->where('timestamp', '<=', $to);
            }
        } elseif ($on) {
            $query->where('timestamp', '=', $on);
        } else {
            // If no time is selected, use default
            $query->where('timestamp', '>=', strtotime('7 days ago'));
        }
        return $query;
    }
}
