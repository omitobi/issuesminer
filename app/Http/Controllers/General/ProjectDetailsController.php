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


//        $project_details['issues_labels'] = $this->setIssuesLabels()->getIssuesLabels();
//        $project_details['files_types'] = $this->setFileTypes()->getFileTypes(); //command line in _notes
//        $project_details['languages'] = $this->setLanguages()->getLanguages();
        $project_details['main_branch'] = 'master';
        $project_details['total_developers'] = $this->setTotalDevelopers()->getTotalDevelopers();
       /* $project_details['total_commits'] = $this->setTotalCommits()->getTotalCommits();
        $project_details['total_issues'] = $this->setTotalIssues()->getTotatlIssues();
        $project_details['total_bug_issues'] = $this->setTotalBugIssues()->geTotalBugIssues();
        $project_details['total_closed_bug_issues'] = $this->setTotalClosedBugIssues()->getTotalClosedBugIssues();
        $project_details['total_created_files'] = $this->setTotalCreatedFiles()->getTotalCreatedFiles();
        $project_details['total_modified_files'] = $this->setTotalModifiedFiles()->getTotalModifiedFiles();
        $project_details['total_deleted_files'] = $this->setTotalDeletedFiles()->getTotalDeletedFiles();
        $project_details['total_deletions'] = $this->setTotalDeletions()->getTotalDeletions();
        $project_details['total_addition'] = $this->setTotalAdditions()->getTotalAdditions();*/

        return $project_details;
        $project->projectDetails()->updateOrCreate(['project_id' => $project->id],
            [

            ]);
    }


}