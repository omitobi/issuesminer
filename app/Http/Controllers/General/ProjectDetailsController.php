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
use Illuminate\Support\Facades\Validator;


class ProjectDetailsController extends ProjectUtility
{
    public function load(Request $request)
    {
        $project_name = $request->only(['project_name']);

        $v = Validator::make($project_name,
            [
                'project_name' => 'required'
            ]
        );

        if($v->fails()){ return $this->respond($v->errors()->all()[0]); }

        if(!$project = Project::where('name', $project_name['project_name'])->first()){
            return $this->respond(" '{$project_name}' does not exist", 404);
        }


        $this->setProject($project);
        $project_details['issues_labels'] = $this->setIssuesLabels()->getIssuesLabels();
        $project_details['languages'] = $this->setLanguages()->getLanguages();

        return $project_details;
        $project->projectDetails()->updateOrCreate(['project_id' => $project->id],
            [

            ]);
    }


}