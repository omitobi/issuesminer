<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:48
 */
namespace App\Http\Controllers\VCS;


use App\Project;
use App\Utilities\CollectionUtility;
use  \App\Utilities\Utility;
use App\VCSModels\VCSEstimation;
use App\VCSModels\VCSProject;
use App\VCSModels\VCSSystem;
use Carbon\Carbon;
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

        $results = [];
        $gen_count = 0;


        if(in_array($project->Id, [3, 1])){

            $revisions = $project->vcsFileRevisions()->orderBy('Date','asc')->take(1000)->with('vcsFileType')->with('vcsFileExtension')->get();
            $revise = $this->revise(
                $revisions,
                $project
            );
            return $this->respond($this->estimations);
        }


        $project->vcsFileRevisions()->orderBy('Date','asc')->with('vcsFileType')->with('vcsFileExtension')->chunk(2000, function ($revisions)
        use (
            &$estimations,
            &$imp_f_count,
            &$cntplus,
            &$_revisions_by_imp,
            $gen_count
        ){

            $developers = $revisions->unique('CommitterId');
//            $results = array_merge($results, $developers);

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



                $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', 0, 'on');
                $this->populateEstimations($date, 'Avg_Previous_OO_Commits', 0, 'on');
                $this->populateEstimations($date, 'Avg_Previous_XML_Commits', 0, 'on');
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

                    if ($xml = $_revision->vcsFileType->isXML) {
                        if (!isset($_revisions_by_imp[$date]['Avg_Previous_XML_Commits'])) {
                            $cntplus['axmc']++;
                        }
                        $_revisions_by_imp[$_revision->Date]['Avg_Previous_XML_Commits'] =  $imp_f_count['axmc'] ?  $imp_f_count['axmc']/$cntplus['axmc'] : 0;
                        $this->populateEstimations($date, 'Avg_Previous_XML_Commits', ( $imp_f_count['axmc'] ?  $imp_f_count['axmc']/$cntplus['axmc'] : 0), 'no');
                        $imp_f_count['axmc'] += $xml;
                    }
                }


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


                foreach ($developers as $developer)
                {
                    if($developer['Date'] === $date){
                        $this->populateEstimations($date, 'Total_Developers', $gen_count, 'on');
                        $gen_count++;
                    }

                }


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
//            $others  = $this->array_extract($this->estimations, [
//                'Date',
//                'ProjectId',
//                'Avg_Previous_Imp_Commits',
//                'Avg_Previous_OO_Commits',
//                'Avg_Previous_XML_Commits',
//                'Avg_Previous_XSL_Commits',
//                'Committer_Previous_Commits',
//                'Committer_Previous_Imp_Commits',
//                'Committer_Previous_OO_Commits',
//                'Committer_Previous_XML_Commits',
//                'Committer_Previous_XSL_Commits',
//                'Developers_On_Project_To_Date',
//                'Imp_Developers_On_Project_To_Date',
//                'Imperative_Files',
//                'OO_Developers_On_Project_To_Date',
//                'OO_Files',
//                'Total_Developers',
//                'Total_Imp_Developers',
//                'Total_OO_Developers',
//                'Total_XML_Developers',
//                'Total_XSL_Developers',
//                'XML_Developers_On_Project_To_Date',
//                'XML_Files',
//                'XSL_Developers_On_Project_To_Date',
//                'XSL_Files'
//            ]);
////            $this->results = $this->updatesOrInserts($others, new VCSEstimation());

//            $this->results = $others;
        });

//        $result = $this->allOrInsert($this->estimations, new VCSEstimation());
//        if(!is_bool($result)){
        $this->insertOrUpdate(array_values($this->estimations), 'VCSEstimations');
//        }

        return $this->respond( $this->estimations );
//        return $this->respond( ['ProjectId' => $project->Id, 'Estimations' => $this->estimations] );
    }



    function populateEstimations( $date, $field, $value, $action='add' )
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

    protected function array_extract( $arrays, $keys, $size = 0, $depth = 1 )
    {
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



    function updatesOrInserts( $attributes, $model, $update = true )
    {
        $result = [];
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
            ->each( function ($_model) use ($attr, $first_keys,  &$available_for_new, $update) {
//
            $available_for_new[] = $_model->{$first_keys[0]} ;
            if($update){
                $_model->update( $attr[ $_model->{$first_keys[0]} ] );
            }
//
            });  //this may need chunking so to speed things up

        if(!count($available_for_new) || count($available_for_new) !== count($attr)){
            $others = array_except($attr, $available_for_new);
             $model->insert(
                $others
            );
            return true;
        }

        $result = array_only($attr, $available_for_new);
        return $result;
    }


    /**
     * Retrieve all models that is existing matches or update
     *
     * @param $attributes
     * @param $model
     * @return array|bool
     */
    public function allOrInsert( $attributes, $model )
    {
        return $this->updatesOrInserts( $attributes, $model, false );
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
    public function insertOrUpdate(array $rows, $table){
//        $table = \DB::getTablePrefix().with(new $this->table)->getTable();
//        $table = self::getTable();


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

























































    function revise($revisions, $project)
    {
        $gen_count = 0;

        $imp_f_count = ['aic' => 0, 'aooc' => 0, 'axmc' => 0, 'axlc' => 0];
        $cntplus = ['aic' => 0, 'aooc' => 0, 'axmc' => 0, 'axlc' => 0];

        $_revisions_by_imp = [];
        $dev_size = 0;

        $developers = $revisions->unique('CommitterId');
//        $mperative_commits = $revisions->where('vcsFileType.IsImperative', 1)->unique('CommitterId');
//        $this->estimations = $mperative_commits;
//        return ;
        $_revisions_by_date = $revisions->groupBy('Date')->all();
        foreach ($_revisions_by_date as $date =>  $revision)
        {

            $this->populateEstimations($date, 'ProjectId', $project->Id, 'normal' );
            $this->populateEstimations($date, 'Date', $date, 'normal' );


            $dev_size =  $developers->filter( function ($devs) use ($date) {
                return CollectionUtility::whereDate($devs->Date, '<=', $date);
            })->count();
//                $_revisions_by_imp = $revision->where('vcsFileType.IsOO', '=', 1)->unique('CommitId')->count();
//                $this->estimations = $_revisions_by_imp;
//                return false;
//                $imp_f_count = $imp_f_count + ($revision->vcsFileType->IsImperative) ? 1 : 0;
//                $this->populateEstimations($date, 'Imperative_Files', $revision->count());
//                $this->populateEstimations($date, 'ProjectDateRevisionId', 'normal' );
            $imperative_for_day = $revision->where('vcsFileType.IsImperative', 1)->count();
            $oo_for_day = $revision->where('vcsFileType.IsOO', 1)->count();
            $xml_for_day = $revision->where('vcsFileType.IsXML', 1)->count();


            $imp_f_count['aic'] += (($imperative_for_day > 0) ? 1 : 0);
            $imp_f_count['aooc'] += (($oo_for_day > 0) ? 1 : 0);
            $imp_f_count['axmc'] += (($xml_for_day > 0) ? 1 : 0);

            $cntplus['aic'] += $imperative_for_day;
            $cntplus['aooc'] += $oo_for_day;
            $cntplus['axmc'] += $xml_for_day;

            $previous_commits = $revisions->where('CommitterId', $revision->last()->CommitterId)->whereDate('Date', '<', $date);
//            $xls_for_day = $revision->where('vcsFileType.IsImperative', 1)->count();

            $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', $imp_f_count['aic']/$dev_size, 'on');
            $this->populateEstimations($date, 'Avg_Previous_OO_Commits', $imp_f_count['aooc']/$dev_size, 'on');
            $this->populateEstimations($date, 'Avg_Previous_XML_Commits', $imp_f_count['axmc']/$dev_size, 'on');
            $this->populateEstimations($date, 'Avg_Previous_XSL_Commits', 0);

            $this->populateEstimations($date, 'Committer_Previous_Commits', $previous_commits->count(), 'on');
            $this->populateEstimations($date, 'Committer_Previous_Imp_Commits', $previous_commits->where('vcsFileType.IsImperative', 1)->count(), 'on');
            $this->populateEstimations($date, 'Committer_Previous_OO_Commits', $previous_commits->where('vcsFileType.IsOO', 1)->count(), 'on');
            $this->populateEstimations($date, 'Committer_Previous_XML_Commits', $previous_commits->where('vcsFileType.IsXML', 1)->count(), 'on');
            $this->populateEstimations($date, 'Committer_Previous_XSL_Commits', 0);
            $this->populateEstimations($date, 'Developers_On_Project_To_Date', $dev_size);
            $this->populateEstimations($date, 'Imp_Developers_On_Project_To_Date', 0);

            $this->populateEstimations($date, 'Imperative_Files', $cntplus['aic'], 'abc');
            $this->populateEstimations($date, 'OO_Developers_On_Project_To_Date', 0);
            $this->populateEstimations($date, 'OO_Files', $cntplus['aooc']);
//            $this->populateEstimations($date, 'Total_Imp_Developers', $gen_count, 'on');
            $this->populateEstimations($date, 'Total_Developers', $dev_size);

            $this->populateEstimations($date, 'Total_Imp_Developers', 0);
            $this->populateEstimations($date, 'Total_OO_Developers', 0);
            $this->populateEstimations($date, 'Total_XML_Developers', 0);
            $this->populateEstimations($date, 'Total_XSL_Developers', 0);
            $this->populateEstimations($date, 'XML_Developers_On_Project_To_Date', 0);
            $this->populateEstimations($date, 'XML_Files', $cntplus['axmc']);
            $this->populateEstimations($date, 'XSL_Developers_On_Project_To_Date', 0);
            $this->populateEstimations($date, 'XSL_Files', 0);


            $this->populateEstimations($date, 'Total_Imp_Developers',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsImperative && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'Imp_Developers_On_Project_To_Date',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsImperative && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'Total_OO_Developers',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsOO && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'OO_Developers_On_Project_To_Date',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsOO && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'Total_XML_Developers',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsXML && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'XML_Developers_On_Project_To_Date',
                $developers->filter( function ($devs) use ($date) {
                    return $devs->vcsFileType->IsXML && CollectionUtility::whereDate($devs->Date, '<=', $date);
                })->count()
                , 'on');
            $this->populateEstimations($date, 'Total_XSL_Developers', $gen_count, 'on');



//                $this->populateEstimations($date, 'XLS_Files', $revision->where('vcsFileType.isXML', 1)->count());


        }

//        $this->insertOrUpdate(array_values($this->estimations), 'VCSEstimations');

        return $this->respond( $this->estimations );

    }

}