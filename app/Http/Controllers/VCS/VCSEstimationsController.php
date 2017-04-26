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
use App\VCSModels\VCSEstimation;
use App\VCSModels\VCSProject;
use App\VCSModels\VCSSystem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class VCSEstimationsController extends Utility
{


    protected $estimations;
    protected $results;



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
        $imp_f_count = ['aic' => 0, 'aooc' => 0];
        $cntplus = ['aic' => 0, 'aooc' => 0];
        $_revisions_by_imp = [];
        $project->vcsFileRevisions()->orderBy('Date','asc')->with('vcsFileType')->chunk(6000, function ($revisions)
        use (
            &$estimations,
            &$imp_f_count,
            &$cntplus,
            &$_revisions_by_imp
        ){

            $_revisions_by_date = $revisions->groupBy('Date')->all();
            foreach ($_revisions_by_date as $date =>  $revision)
            {
//                $_revisions_by_imp = $revision->where('vcsFileType.IsOO', '=', 1)->unique('CommitId')->count();
//                $this->estimations = $_revisions_by_imp;
//                return false;
//                $imp_f_count = $imp_f_count + ($revision->vcsFileType->IsImperative) ? 1 : 0;
//                $this->populateEstimations($date, 'Imperative_Files', $revision->count());
//                $this->populateEstimations($date, 'ProjectDateRevisionId', 'normal' );
                $this->populateEstimations($date, 'ProjectId', $revision->first()->ProjectId, 'normal' );
                $this->populateEstimations($date, 'Date', $date, 'normal' );



                $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', $revision->where('vcsFileType.IsImperative',  1)->unique('CommitId')->count());
                $this->populateEstimations($date, 'Avg_Previous_OO_Commits', $revision->where('vcsFileType.IsOO', 1)->unique('CommitId')->count());
                foreach ($revision as $key => $_revision) {
                    if ($imperative = $_revision->vcsFileType->IsImperative) {
                        if (!isset($_revisions_by_imp[$date]['Avg_Previous_Imp_Commits'])) {
                            $cntplus['aic']++;
                        }
                        $_revisions_by_imp[$_revision->Date]['Avg_Previous_Imp_Commits'] = $imp_f_count['aic'] ?  $imp_f_count['aic']/$cntplus['aic'] : 0;
                        $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', ( $imp_f_count['aic'] ?  $imp_f_count['aic']/$cntplus['aic'] : 0), 'no');
//                    $_revisions_by_imp[$revision->Date][$key] = $imperative;
                        $imp_f_count['aic'] += $imperative;
                    }

                    if ($oo = $_revision->vcsFileType->IsOO) {
                        if (!isset($_revisions_by_imp[$date]['Avg_Previous_OO_Commits'])) {
                            $cntplus['aooc']++;
                        }
                        $_revisions_by_imp[$_revision->Date]['Avg_Previous_OO_Commits'] =  $imp_f_count['aooc'] ?  $imp_f_count['aooc']/$cntplus['aooc'] : 0;
                        $this->populateEstimations($date, 'Avg_Previous_OO_Commits', ( $imp_f_count['aooc'] ?  $imp_f_count['aooc']/$cntplus['aooc'] : 0), 'no');
                        $imp_f_count['aooc'] += $oo;
                    }
                }


                $this->populateEstimations($date, 'Avg_Previous_XML_Commits', $revision->where('vcsFileType.IsXML', 1)->unique('CommitId')->count());
                $this->populateEstimations($date, 'Avg_Previous_XSL_Commits', 0);

                $this->populateEstimations($date, 'Committer_Previous_Commits', 0);
                $this->populateEstimations($date, 'Committer_Previous_Imp_Commits', 0);
                $this->populateEstimations($date, 'Committer_Previous_OO_Commits', 0);
                $this->populateEstimations($date, 'Committer_Previous_XML_Commits', 0);
                $this->populateEstimations($date, 'Committer_Previous_XSL_Commits', 0);
                $this->populateEstimations($date, 'Developers_On_Project_To_Date', 0);
                $this->populateEstimations($date, 'Imp_Developers_On_Project_To_Date', 0);
                $this->populateEstimations($date, 'Imperative_Files', $revision->where('vcsFileType.IsImperative', 1)->count());
                $this->populateEstimations($date, 'OO_Developers_On_Project_To_Date', 1);
                $this->populateEstimations($date, 'OO_Files', $revision->where('vcsFileType.IsOO', 1)->count());
                $this->populateEstimations($date, 'Total_Developers', 0);
                $this->populateEstimations($date, 'Total_Imp_Developers', 0);
                $this->populateEstimations($date, 'Total_OO_Developers', 0);
                $this->populateEstimations($date, 'Total_XML_Developers', 0);
                $this->populateEstimations($date, 'Total_XSL_Developers', 0);
                $this->populateEstimations($date, 'XML_Developers_On_Project_To_Date', 0);
                $this->populateEstimations($date, 'XML_Files', $revision->where('vcsFileType.isXML', 1)->count());
                $this->populateEstimations($date, 'XSL_Developers_On_Project_To_Date', 0);
                $this->populateEstimations($date, 'XSL_Files', 0);
//                $this->populateEstimations($date, 'XLS_Files', $revision->where('vcsFileType.isXML', 1)->count());


            }

//            $_revisions_by_imp = [];
//            $_revisions_by_ComitId = $revisions->groupBy('CommitId')->all();
//            foreach ($_revisions_by_ComitId as $key => $_revision)
//            {
////                $_revisions_by_imp[$_revision->Date]['isNormal'] = $key;
//                $imperative = $_revision->where('vcsFileType.IsImperative',  1);
//                if($imperative->count()){
//                    $_revisions_by_imp[$_revision->first()->Date]['isImperative'] = $imp_f_count;
////                    $_revisions_by_imp[$_revision->first()->Date][$key] = $imperative;
//                    $imp_f_count += $imperative->count();
////                    break;
//                }
//
//            }
           /* $cntplus = 0;
            foreach ($revisions as $key => $_revision)
            {
                if($imperative = $_revision->vcsFileType->IsImperative){
                    if(!isset($_revisions_by_imp[$_revision->Date])){
                        $cntplus++;
                    }
                    $_revisions_by_imp[$_revision->Date]['Avg_Previous_Imp_Commits'] = $imp_f_count ?  $imp_f_count/$cntplus : 0;
                    $_revisions_by_imp[$_revision->Date][$key] = $imperative;
                    $imp_f_count += $imperative;

//                    break;
                }

            }
            $this->results = $_revisions_by_imp;
//            $this->results = array_where($_revisions_by_imp, function($array){
//                return $array['isImperative'] === 1;
//            });
            $estimations = $this->estimations;*/

//            $dates  = array_keys($this->estimations);



//            return false;
//            return false;
        });
//        $estimations_values = array_values($this->estimations);
//        list($keys, $estimations_values) = array_divide($this->estimations);
//        $dates  = array_keys($this->estimations);

        $others  = $this->array_extract($this->estimations, [
            'Date',
            'ProjectId',
            'Avg_Previous_Imp_Commits',
            'Avg_Previous_OO_Commits',
            'Avg_Previous_XML_Commits',
            'Avg_Previous_XSL_Commits',
            'Committer_Previous_Commits',
            'Committer_Previous_Imp_Commits',
            'Committer_Previous_OO_Commits',
            'Committer_Previous_XML_Commits',
            'Committer_Previous_XSL_Commits',
            'Developers_On_Project_To_Date',
            'Imp_Developers_On_Project_To_Date',
            'Imperative_Files',
            'OO_Developers_On_Project_To_Date',
            'OO_Files',
            'Total_Developers',
            'Total_Imp_Developers',
            'Total_OO_Developers',
            'Total_XML_Developers',
            'Total_XSL_Developers',
            'XML_Developers_On_Project_To_Date',
            'XML_Files',
            'XSL_Developers_On_Project_To_Date',
            'XSL_Files'
        ]);
////            $this->results = $this->updatesOrInserts($others, new VCSEstimation());
        VCSEstimation::insertOrUpdates($others, 'VCSEstimations');
        return $this->respond( $others );
//        return $this->respond( ['ProjectId' => $project->Id, 'Estimations' => $this->estimations] );
    }

    function populateEstimations($date, $field, $value, $action='add')
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
        if($action === 'add'){
            $this->estimations[$date][$field] += $value;
            return ;
        }
        $this->estimations[$date][$field]  = $value;
    }

    protected function array_extract($arrays, $keys, $size = 0, $depth = 1)
    {
        $result = [];
        $result = [];
        $depth_attained = 1;
        if(!is_array($keys)){
            $keys = [$keys];
        }
        foreach ($keys as $k => $key )
        {
            $cnt = 0;
            foreach ($arrays as $idx => $array)
            {
                $result[$cnt][$key] = $array[$key];
                if($size) {
                    if($size === $cnt+1) break;
                }
                $cnt++;
            }
        }

        return $result;
    }



    function updatesOrInserts($attributes, $model)
    {
        $available_for_new = [];

        $colection = collect($attributes);

        $first_attr = array_first($attributes);

        $first_keys = array_keys($first_attr);

        $attr = $colection->keyBy($first_keys[0])->all();

        $unique_attribute = $this->array_extract($attr, $first_keys[0]);

        $unique_attribute_values = array_flatten($unique_attribute);

//        $available_for_new = $model->whereIn($first_keys[0], $unique_attribute_values)->pluck($first_keys[0])->toArray();
//        $results = $available_for_new;

//        $model->whereIn( $first_keys, $attributes)
//            ->each( function ($_model) use ($attr, $first_keys,  &$available_for_new) {
//
//            $available_for_new[] = $_model->{$first_keys[0]} ;
//
//            $_model->update( $attr[ $_model->{$first_keys[0]} ] );
//
//            });  //this may need chunking so to speed things up

        $model->wheresIn($first_keys, $attributes)
            ->each( function ($_model) use ($attr, $first_keys,  &$available_for_new) {
//
            $available_for_new[] = $_model->{$first_keys[0]} ;
//
            $_model->update( $attr[ $_model->{$first_keys[0]} ] );
//
            });  //this may need chunking so to speed things up

        if(!count($available_for_new) || count($available_for_new) !== count($attr)){
            $others = array_except($attr, $available_for_new);
            $model->insert(
                $others
            );
        }
    }



}