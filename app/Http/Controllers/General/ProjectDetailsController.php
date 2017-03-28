<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 27/03/2017
 * Time: 21:44
 */

namespace App\Http\Controllers\General;


use App\Project;
use App\Utilities\ProjectUtility;
use Illuminate\Http\Request;


class ProjectDetailsController extends ProjectUtility
{
    public function load(Request $request)
    {
        $project_name = $request->get('project_name');

        if(!$project = Project::where('name', $project_name)->first()){
            return $this->respond(" '{$project_name}' does not exist", 404);
        }


        $this->setProject($project);
        $project_details['issues_labels'] = $this->setIssuesLabels()->getIssuesLabels();

        return $project_details;
        $project->projectDetails()->updateOrCreate(['project_id' => $project->id],
            [

            ]);
    }


}