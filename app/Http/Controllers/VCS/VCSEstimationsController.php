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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class VCSEstimationsController extends Utility
{


    protected $estimations;



    public function loadAll(Request $request)
    {
        if(!$_project = $request->get('project_name')) {
            return response(['error' => 'invalid project_name'], 400);
        }
//         sleep ( 61 );
        if(!$project = VCSProject::where('name', $_project)
            ->orWhere('id', $_project)->first()) {
            return $this->respond('Project does not exist', 404);
        }
        $estimations = [];
        $imp_f_count = 0;
        $project->vcsFileRevisions()->orderBy('Date','asc')->with('vcsFileType')->chunk(2500, function ($revisions) use (&$estimations, $imp_f_count){

            $_revisions = $revisions->groupBy('Date')->all();
            foreach ($_revisions as $date =>  $revision)
            {
//                $imp_f_count = $imp_f_count + ($revision->vcsFileType->IsImperative) ? 1 : 0;
//                $this->populateEstimations($date, 'Imperative_Files', $revision->count());
                $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', $revision->unique('CommitId')->where('vcsFileType.IsImperative', 1)->count());
                $this->populateEstimations($date, 'Avg_Previous_OO_Commits', 1);
                $this->populateEstimations($date, 'Avg_Previous_XML_Commits', 1);
                $this->populateEstimations($date, 'Avg_Previous_XSL_Commits', 1);
                $this->populateEstimations($date, 'Committer_Previous_Commits', 1);
                $this->populateEstimations($date, 'Committer_Previous_Imp_Commits', 1);
                $this->populateEstimations($date, 'Committer_Previous_OO_Commits', 1);
                $this->populateEstimations($date, 'Committer_Previous_XML_Commits', 1);
                $this->populateEstimations($date, 'Committer_Previous_XSL_Commits', 1);
                $this->populateEstimations($date, 'Developers_On_Project_To_Date', 1);
                $this->populateEstimations($date, 'Imp_Developers_On_Project_To_Date', 1);
                $this->populateEstimations($date, 'Imperative_Files', $revision->where('vcsFileType.IsImperative', 1)->count());
                $this->populateEstimations($date, 'OO_Developers_On_Project_To_Date', 1);
                $this->populateEstimations($date, 'OO_Files', $revision->where('vcsFileType.IsOO', 1)->count());
                $this->populateEstimations($date, 'Total_Developers', 1);
                $this->populateEstimations($date, 'Total_Imp_Developers', 1);
                $this->populateEstimations($date, 'Total_OO_Developers', 1);
                $this->populateEstimations($date, 'Total_XML_Developers', 1);
                $this->populateEstimations($date, 'Total_XSL_Developers', 1);
                $this->populateEstimations($date, 'XML_Developers_On_Project_To_Date', 1);
                $this->populateEstimations($date, 'XML_Files', $revision->where('vcsFileType.isXML', 1)->count());
                $this->populateEstimations($date, 'XSL_Developers_On_Project_To_Date', 1);
                $this->populateEstimations($date, 'XSL_Files', 1);
//                $this->populateEstimations($date, 'XLS_Files', $revision->where('vcsFileType.isXML', 1)->count());

            }
        });


        return $this->respond( ['ProjectId' => $project->Id, 'Estimations' => $this->estimations] );
    }

    function populateEstimations($date, $field, $value)
    {
        if(!$this->estimations) {
            $this->estimations = [];
        }
        if(!isset($this->estimations[$date])){

            $this->estimations[$date] = [$field => $value];
            return ;
        }
        if(!isset($this->estimations[$date][$field])){

            $this->estimations[$date][$field]  = $value;
            return ;
        }
        $this->estimations[$date][$field] += $value;
    }

}