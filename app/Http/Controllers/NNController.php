<?php

namespace App\Http\Controllers;

use App\Http\Controllers\CSVController;
use App\Settings as Settings;
use App\Project as Project;

/**
 * Class NNController
 *
 * This class controls the Python code
 * that implements the Neural Network.
 *
 * @package App\Http\Controllers
 */
class NNController extends Controller
{
    private $network = 'cd $(pwd)/python; python nn.py';

    public function NNStats()
    {
        //return Settings::get('nn_stats', array());
        return array(
            'Samples' => 200,
            'TP' => 80,
            'TN' => 79,
            'FP' => 6,
            'FN' => 35,
            'Accuracy' => '79.5%'
        );
    }

    public function projects()
    {
        $projects = Project::all();
        $inactive_projects = Settings::get('nn_active_projects', array());

        $data = array();
        foreach ($projects as $project) {
            $count = \DB::table($project->id . '_usage')
                ->count();
            $data[] = array(
                'project' => $project->name,
                'samples' => $count,
                'active' => !in_array($project->id, $inactive_projects)
            );
        }
        return $data;
    }

    public function predict()
    {

    }

    public function train()
    {

    }

    private function command($command)
    {
        return shell_exec($this->network . ' ' . $command);
    }

    private function trainNetwork()
    {
        shell_exec($this->command('verify'));
    }

    public function log()
    {
        var_dump($this->network . ' verify');
        var_dump($this->command('verify'));
    }
}