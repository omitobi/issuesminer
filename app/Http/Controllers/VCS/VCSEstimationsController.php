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
        $imp_f_count = 0;
        $project->vcsFileRevisions()->orderBy('Date','asc')->with('vcsFileType')->chunk(6000, function ($revisions) use (&$estimations, $imp_f_count){

            $_revisions_by_date = $revisions->groupBy('Date')->all();
            foreach ($_revisions_by_date as $date =>  $revision)
            {
                $_revisions_by_imp = $revision->where('vcsFileType.IsOO', '=', 1)->unique('CommitId')->count();
//                $this->estimations = $_revisions_by_imp;
//                return false;
//                $imp_f_count = $imp_f_count + ($revision->vcsFileType->IsImperative) ? 1 : 0;
//                $this->populateEstimations($date, 'Imperative_Files', $revision->count());
//                $this->populateEstimations($date, 'ProjectDateRevisionId', 'normal' );
                $this->populateEstimations($date, 'ProjectId', $revision->first()->ProjectId, 'normal' );
                $this->populateEstimations($date, 'Date', $date, 'normal' );
                $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', $revision->where('vcsFileType.IsImperative',  1)->unique('CommitId')->count());
                $this->populateEstimations($date, 'Avg_Previous_OO_Commits', $revision->where('vcsFileType.IsOO', 1)->unique('CommitId')->count());
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
            $estimations = $this->estimations;
            $dates  = array_keys($this->estimations);
            /*$only  = $this->array_extract($this->estimations, 'Date', 1, 2);
            */
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
            $this->results = $this->insertOrUpdate($others, 'VCSEstimations');

//            Model::unguard();
//            $available_for_new = [];
//            $available_for_new = VCSEstimation::whereIn('Date', $dates)->pluck('Date')->toArray();
//            $this->results = $available_for_new;
////            each( function ($model) use ($estimations, &$available_for_new){
////                $available_for_new[] = $model->Date;
//////                $es_mod = new VCSEstimation();
//////                $es_mod->fill($estimations[$model->Date]);
//////                if(!$es_mod->exists)
////                $model->update($estimations[$model->Date]);
////            });
//
//           if(!count($available_for_new) || count($available_for_new) !== count($estimations)){
//               $others = array_except($this->estimations, $available_for_new);
//               VCSEstimation::insert(
//                   $others
//               );
//           }
//            Model::reguard();
            return false;
        });
//        $estimations_values = array_values($this->estimations);
//        list($keys, $estimations_values) = array_divide($this->estimations);
//        $dates  = array_keys($this->estimations);

        return $this->respond($this->results );
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

    /**
     * Mass (bulk) insert or update on duplicate for Laravel 4/5
     *ref: http://stackoverflow.com/a/27593831/5704410
     * src: https://gist.github.com/RuGa/5354e44883c7651fd15c
     * insertOrUpdate([
     *   ['id'=>1,'value'=>10],
     *   ['id'=>2,'value'=>60]
     * ]);
     *
     *
     * @param array $rows
     */
    function insertOrUpdate(array $rows, $table){
//        $table = \DB::getTablePrefix().with(new self)->getTable();


        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    ).')';
            } , $rows )
        );

        $updates = implode( ',',
            array_map( function( $value ) { return "$value = VALUES($value)"; } , array_keys($first) )
        );


        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::statement( $sql );
    }

}