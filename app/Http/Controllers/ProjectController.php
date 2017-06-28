<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use App\Settings as Settings;
use App\Project as Project;

/**
 * Class ProjectController
 *
 * This class manages project-related functions:
 * - Getting a list of all projects
 * - Getting information for a single project
 * - Creating a project
 * - Updating a project
 * - Removing a project
 *
 * @package App\Http\Controllers
 */
class ProjectController extends Controller
{
    private $settings;

    /**
     * Returns all projects in alphabetical order
     *
     * @return mixed
     */
    public function getProjects()
    {
        return Project::select()->orderBy('name')->get();
    }

    /**
     * Returns basic information for a project.
     *
     * @param  string $slug Project slug
     * @return string
     */
    public function getProject($slug)
    {
        $this->verifyProject($slug);

        $this->project['projectkey'] = $this->project->getKey();
        $this->project['forecast'] = Settings::get($this->project->id . '_forecast_values');
        return $this->project->toArray();
    }

    /**
     * Updates the project with newly received settings.
     *
     * @param  Request $request Object with project settings
     * @param  string  $slug    Project slug
     * @return int
     */
    public function updateProject(Request $request, $slug)
    {
        $this->verifyProject($slug);
        $this->settings = json_decode($request->getContent());

        if ($this->settings->allowEditProjectKey) {
            $this->project->key = $this->settings->projectkey;
        }

        if ($this->settings->allowEditProjectSlug) {
            $this->project->slug = $this->settings->slug;
        }

        $this->project->url = $this->settings->url;
        $this->project->name = $this->settings->name;
        $this->project->save();

        if ($this->settings->forecast) {
            Settings::set($this->project->id . '_forecast_values', $this->settings->forecast);
        }

        return 200;
    }

    /**
     * Calls the createNewProject function and returns the generated
     * project slug.
     *
     * @param  Request $request Object with project data
     * @return string
     */
    public function addProject(Request $request)
    {
        $project = json_decode($request->getContent());
        if ($this->createNewProject($project)) {
            return str_slug($request->input('name'));
        }
        return 'error';
    }

    /**
     * Creates a new project and calls functions to generate
     * the new database tables.
     *
     * @param  object $newproject Object containing data for the new project
     * @return bool
     */
    private function createNewProject($newproject)
    {
        $project = new Project;
        $project->name = $newproject->name;
        $project->slug = str_slug($newproject->name, '-');
        $project->key = $newproject->key;
        $project->url = $newproject->url;
        $project->timestamps = false;
        $project->save();

        $this->generateUsageTable($project->id);
        $this->generateDBTable($project->id);
        $this->generatePluginTable($project->id);

        return true;
    }

    /**
     * Creates a slug for a project name.
     *
     * @param  Request $request Object containing the project name
     * @return string
     */
    public function createSlug(Request $request)
    {
        $project = json_decode($request->getContent());
        return str_slug($project->name, '-');
    }

    /**
     * Removes a project from the database and the data belonging
     * to it.
     *
     * @param  Request $request Object containing the project name
     * @return int
     */
    public function removeProject(Request $request)
    {
        $project = json_decode($request->getContent());
        $this->verifyProject($project->slug);

        \Schema::dropIfExists($this->project->id . "_usage");
        \Schema::dropIfExists($this->project->id . "_db");
        \Schema::dropIfExists($this->project->id . "_plugins");
        $this->project->delete();

        return 200;
    }

    /**
     * Creates a new usage table based on a project id.
     *
     * @param int $id Project ID
     */
    private function generateUsageTable($id)
    {
        \Schema::create(
            $id . '_usage',
            function (Blueprint $table) {
                $table->integer('timestamp');
                $table->float('hdd', 5, 2);
                $table->float('ram', 5, 2);
                $table->float('cpu', 5, 2);
                $table->float('rx', 8, 2);
                $table->float('tx', 8, 2);
                $table->float('page', 9, 3);
            }
        );
    }

    /**
     * Creates a new database table based on a project id.
     *
     * @param int $id Project ID
     */
    private function generateDBTable($id)
    {
        \Schema::create(
            $id . '_db',
            function (Blueprint $table) {
                $table->integer('timestamp');
                $table->string('table', 64);
                $table->float('size', 8, 2);
            }
        );
    }

    /**
     * Creates a new plugins table based on a project id.
     *
     * @param int $id Project ID
     */
    private function generatePluginTable($id)
    {
        \Schema::create(
            $id . '_plugins',
            function (Blueprint $table) {
                $table->integer('timestamp');
                $table->string('name', 64);
                $table->string('version', 16)->nullable();
                $table->string('slug', 64);
                $table->string('uri', 120);
                $table->boolean('active')->nullable();
                $table->string('new_version', 16)->nullable();
            }
        );
    }
}
