<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 05/04/2017
 * Time: 20:13
 */

namespace App\Http\Controllers\General;


use App\Project;
use Illuminate\Http\Request;
use App\Utilities\ProjectUtility;

class DevelopersController extends ProjectUtility
{
    public function load(Request $request)
    {
        if(!$project_name = $request->get('project_name')) {
            return response(['error' => 'invalid project_name'], 400);
        }
//         sleep ( 61 );
        if(!$project = Project::where('name', $project_name)->first()) {
            return $this->respond('Project does not exist', 404);
        }

//        return $request->all();

        $this->setProject($project);
        $dev_url = !$request->get('url') ? $this->project->contributors_url : $request->get('url');

        $_project = $this->setTotalDevelopers($dev_url, '&page='.$request->get('page'))->next_;
        return $_project;
    }
}