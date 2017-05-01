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
use App\VCSModels\ProjectDateRevision;
use App\VCSModels\VCSEstimation;
use App\VCSModels\VCSFileRevision;
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

    protected $something;
    public function loadRevisionDates(Request $request)
    {
        if(!$_project = $request->get('project_name')) {
            return response(['error' => 'invalid project_name'], 400);
        }
//         sleep ( 61 );
        if(!$project = VCSProject::where('name', $_project)
            ->orWhere('id', $_project)->first()) {
            return $this->respond('Project does not exist', 404);
        }

//        return $project->vcsFileRevisions()->orderBy('Date','asc')->distinct('Date')->count('Date');
//        return $project->vcsFileRevisions()->orderBy('Date','asc')->whereDate('Date','<=', '2006-03-23')->count();

        $vcsRevisions = $project->vcsFileRevisions()->orderBy('Date','asc')->where('AuthorEmail', '!=', NULL)
        ->where('datetouched', '0')
            ->select([
                'ProjectId',
                'Date',
                'CommitId',
                'AuthorEmail as CommitterId',
                'Id as RevisionId',
                'Extension',
                'FiletypeId'
            ])->chunk(5000, function ($revisions) use ($project){

                if($this->insertOrUpdate($revisions->toArray(), 'ProjectDateRevision' )){

                    VCSFileRevision::whereIn('Id', $revisions->pluck('RevisionId')->toArray())->where(['datetouched' => '0', 'ProjectId' => $project->Id])
                        ->update(['datetouched' => 1]);

                };

            });

       return $this->respond($this->something);
    }



    public function loadAll(Request $request)
    {
        /**
         * Allow up to 5 minutes execution
         */
        ini_set('max_execution_time', 300);


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

//            $revisions = $project->vcsFileRevisions()->orderBy('Date','asc')->take(20)->with('vcsFileType')->with('vcsFileExtension')->get();
            $revisionDates = $project->projectDateRevisions()->where('module_touched', '0')->orderBy('Date','asc')->take(400)->get();
            $revise = $this->revise(
                $project,
                $revisionDates
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



















































    function countTillDate($project, $date, $revisionDate)
    {
//        $result->developers = $distinct->distinct()->count('CommitId');
        $result = collect([]);

        $distinct = $project->vcsFileRevisions()->with('vcsFIleType')
            ->where('Date', '<=', $date) //hopefully laravel didn't do string comparison but allow sql do the job
            ->orderBy('Date', 'asc');
        $vcs_revisions = $distinct->get();

        $committer = $vcs_revisions->where('AuthorEmail', $revisionDate->CommitterId);
        $result->developers = $vcs_revisions->unique('AuthorEmail')->count();

//        $commits_coll = collect($vcs_revisions->unique('CommitId')->toArray());
//        $commitIds = $commits_coll->keyBy('CommitId')->keys();

        $result->imperative = $vcs_revisions->where('vcsFileType.IsImperative', '1')->unique('CommitId')->count();
        $result->oo = $vcs_revisions->where('vcsFileType.IsOO', '1')->unique('CommitId')->count();
        $result->xml = $vcs_revisions->where('vcsFileType.IsXML', '1')->unique('CommitId')->count();
        $result->xsl = $vcs_revisions->where('Extension', '.xsl')->unique('CommitId')->count();

        $result->committer_previous =$committer->unique('CommitId')->count();
        $result->committer_previous_imp = $committer->where('vcsFileType.IsImperative', '1')->unique('CommitId')->count();
        $result->committer_previous_oo = $committer->where('vcsFileType.IsOO', '1')->unique('CommitId')->count();
        $result->committer_previous_xml = $committer->where('vcsFileType.IsXML', '1')->unique('CommitId')->count();
        $result->committer_previous_xsl = $committer->where('Extension', '.xsl')->unique('CommitId')->count();


        $result->imp_files = $vcs_revisions->where('vcsFileType.IsImperative', '1')->unique('Alias')->count();
        $result->oo_files = $vcs_revisions->where('vcsFileType.IsOO', '1')->unique('Alias')->count();
        $result->xml_files = $vcs_revisions->where('vcsFileType.IsXML', '1')->unique('Alias')->count();
        $result->xsl_files = $vcs_revisions->where('Extension', '.xsl')->unique('Alias')->count();

        $result->imp_developers = $vcs_revisions->where('vcsFileType.IsImperative', '1')->unique('AuthorEmail')->count();
        $result->oo_developers =  $vcs_revisions->where('vcsFileType.IsOO', '1')->unique('AuthorEmail')->count();
        $result->xml_developers = $vcs_revisions->where('vcsFileType.IsXML', '1')->unique('AuthorEmail')->count();
        $result->xsl_developers = $vcs_revisions->where('Extension', '.xsl')->unique('AuthorEmail')->count();

        return $result;
    }





    /*
     * Key: Project Date Revision Id
     * Predicted column (needed for training as well): Project Yearly LOC Churn
     */

    function revise($project, $revisionDates)
    {
//        $gen_count = 0;
//
//        $imp_f_count = ['aic' => 0, 'aooc' => 0, 'axmc' => 0, 'axlc' => 0];
//        $cntplus = ['aic' => 0, 'aooc' => 0, 'axmc' => 0, 'axlc' => 0];
//
//        $_revisions_by_imp = [];
//        $dev_size = 0;
//
//        $developers = $revisions->unique('CommitterId');
//        $_revisions_by_date = $revisions->groupBy('Date');

        $revisionchunks = $revisionDates->chunk(200);
        foreach ($revisionchunks as $chunk)
        {
//            $cylce = 0;
            foreach ($chunk as $revisionDate)
            {
                $date = $revisionDate->Date;

                /**
                 * Others follow here
                 */

                $this->populateEstimations($date, 'ProjectId', $revisionDate->ProjectId, 'normal');
                $this->populateEstimations($date, 'ProjectDateRevisionId', $revisionDate->Id, 'normal');

                /**
                 * General counts
                 */

                $_counts = $this->countTillDate($project, $date, $revisionDate);
                $imperative_count = $_counts->imperative;
                $oo = $_counts->oo;
                $xml = $_counts->xml;
                $xls = $_counts->xsl;
                $committer_previous = $_counts->committer_previous;
                $committer_previous_imp = $_counts->committer_previous_imp;
                $committer_previous_oo = $_counts->committer_previous_oo;
                $committer_previous_xml = $_counts->committer_previous_xml;
                $committer_previous_xls = $_counts->committer_previous_xsl;
                $imp_files = $_counts->imp_files;
                $oo_files = $_counts->oo_files;
                $xml_files = $_counts->xml_files;
                $xls_files = $_counts->xsl_files;
                $imp_developers = $_counts->imp_developers;
                $oo_developers = $_counts->oo_developers;
                $xml_developers = $_counts->xml_developers;
                $xls_developers = $_counts->xsl_developers;

                /**
                 * Other derived fields
                 */
                $this->populateEstimations($date, 'Total_Developers', $_counts->developers);
                $this->populateEstimations($date, 'Total_Imp_Developers', $imp_developers, 'on');
                $this->populateEstimations($date, 'Total_OO_Developers', $oo_developers);
                $this->populateEstimations($date, 'Total_XML_Developers', $xml_developers);
                $this->populateEstimations($date, 'Total_XSL_Developers', $xls_developers);

                $this->populateEstimations($date, 'Imp_Developers_On_Project_To_Date', $imp_developers);
                $this->populateEstimations($date, 'OO_Developers_On_Project_To_Date', $oo_developers);
                $this->populateEstimations($date, 'XML_Developers_On_Project_To_Date', $xml_developers);
                $this->populateEstimations($date, 'XSL_Developers_On_Project_To_Date', $xls_developers);

                $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', $imperative_count / $_counts->developers, 'on');
                $this->populateEstimations($date, 'Avg_Previous_OO_Commits', $oo / $_counts->developers, 'on');
                $this->populateEstimations($date, 'Avg_Previous_XML_Commits', $xml / $_counts->developers, 'on');
                $this->populateEstimations($date, 'Avg_Previous_XSL_Commits', $xls / $_counts->developers, 'on');

                $this->populateEstimations($date, 'Committer_Previous_Commits', $committer_previous, 'on');
                $this->populateEstimations($date, 'Committer_Previous_Imp_Commits', $committer_previous_imp, 'on');
                $this->populateEstimations($date, 'Committer_Previous_OO_Commits', $committer_previous_oo, 'on');
                $this->populateEstimations($date, 'Committer_Previous_XML_Commits', $committer_previous_xml, 'on');
                $this->populateEstimations($date, 'Committer_Previous_XSL_Commits', $committer_previous_xls, 'on');

                $this->populateEstimations($date, 'Imperative_Files', $imp_files, 'abc');
                $this->populateEstimations($date, 'OO_Files', $oo_files, 'abc');
                $this->populateEstimations($date, 'XML_Files', $xml_files, 'abc');
                $this->populateEstimations($date, 'XSL_Files', $xls_files, 'abc');

//                $cylce++;
//                if ($cylce === 10): break; endif;
            }

            if($this->insertOrUpdate($this->estimations, 'VCSEstimations')){
                ProjectDateRevision::whereIn(
                    'Id', $chunk->pluck('Id')->toArray()
                )->where([
                    'ProjectId' => $project->Id,
                    'estimation_touched' => '0'
                ])->update([
                    'estimation_touched' => '1'
                ]);
            }
            $this->estimations = [];
        }

//        $this->insertOrUpdate(array_values($this->estimations), 'VCSEstimations');

//        return $this->respond( $this->estimations );

    }

}