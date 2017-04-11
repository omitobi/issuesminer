<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:48
 */
namespace App\Http\Controllers\VCS;


use App\Project;
use  \App\Utilities\Utility;
use App\VCSModels\VCSProject;
use App\VCSModels\VCSSystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProjectsController extends Utility
{
    public function loadAll()
    {
        $system = VCSSystem::where('Name', 'GIT')->first();

        $projects = Project::all();
        $_projects_ = [];

        Model::unguard();
        foreach ($projects as $project)
        {
            $_vproject['Name'] = $project->name;
            $_vproject['Location'] = $project->web_url;
            $_vproject['Type'] = 'web';
            $_projects_[] = $system->VCSProjects()->updateOrCreate($_vproject, $_vproject);
        }
        Model::reguard();

        return $_projects_;
    }
}