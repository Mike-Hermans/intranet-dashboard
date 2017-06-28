<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use App\Http\Controllers\CSVController;

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
        shell_exec('');
    }

    public function log()
    {
        var_dump($this->command(''));
    }
}