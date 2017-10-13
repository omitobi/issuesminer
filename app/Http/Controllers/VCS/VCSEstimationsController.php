<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:48
 */
namespace App\Http\Controllers\VCS;


use App\Commit;
use App\Project;
use App\Utilities\Cachetility;
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
    private $dot_imps = [];
    private $dot_oos = [];
    private $dot_xmls = [];
    private $dot_xsl = '.xsl';


    /**
     * @var \Illuminate\Support\Collection $project_dates_index
     */
    protected  $project_dates_index;

    /**
     * @var \Illuminate\Support\Collection $project_commits_index
     */
    protected  $project_commits_index;

    private $total_dates_count = 0;

    protected  $total_developers = 0;
    protected  $total_imp_developers = 0;
    protected  $total_oo_developers = 0;
    protected  $total_xml_developers = 0;
    protected  $total_xsl_developers = 0;

    protected $estimations;
    protected $results;

    protected $something;


    protected  $cachetility;

    function __construct(Cachetility $cachetility)
    {
        $this->cachetility = $cachetility;
        parent::__construct();
    }




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

        return  $vcsRevisions = $project->vcsFileRevisions()->orderBy('Date','asc')
            ->where('datetouched', 0)->get();
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

                    VCSFileRevision::whereIn('Id', $revisions->pluck('RevisionId')->toArray())
                        ->where(['datetouched' => '0', 'ProjectId' => $project->Id])
                        ->update(['datetouched' => 1]);

                };

            });

       return $this->respond([
        'message' => 'Load successfully .... All Dates into ProjectDateRevision',
        'status' => 'success',
        'extra' => 'covered'
    ]);
    }



    public function loadAll(Request $request)
    {

        if(!$_project = $request->get('project_name')) {
            return response(['error' => 'invalid project_name'], 400);
        }
        if(!$project = VCSProject::where('name', $_project)
            ->orWhere('id', $_project)->first()) {
            return $this->respond('Project does not exist', 404);
        }

        /**
         * Set Total developers
         */

        $this->total_developers = $this->cachetility->getDevelopersCount($project);
        $this->total_imp_developers = $this->cachetility->countDevelopers($project, $this->imp_langs, 'imp');
        $this->total_oo_developers  =  $this->cachetility->countDevelopers($project, $this->oo_langs, 'oo');
        $this->total_xml_developers = $this->cachetility->countDevelopers($project, $this->xmls, 'xml');
        $this->total_xsl_developers = $this->cachetility->countDevelopers($project, ['xsl'], 'xsl');

        $this->project_dates_index = $this->cachetility->getRevisionDateIndex($project);
        $this->total_dates_count = $this->project_dates_index->count();
//        $this->cachetility->clear('vcs_commits_idx_project_'.$project->Id);
        $this->project_commits_index = $this->cachetility->getCommitsIndex($project)->transform(function ($date){
            return str_replace(['T', 'Z'], [' ', ''], $date);
        });

//        dd(json_it($this->project_commits_index->toArray()));
        $this->dot_imps = dot_array($this->imp_langs);
        $this->dot_oos = dot_array($this->oo_langs);
        $this->dot_xmls = dot_array($this->xmls);

        /**
         * Developers end here -- real revision starts
         */
        $revisionDates = $project->projectDateRevisions()
            ->select('Id', 'ProjectId', 'Date', 'CommitId', 'CommitterId')
            ->where('estimation_touched', '0')
            ->orderBy('Date','asc')
            ->take(100)
            ->get();

        /**
         * Allow up to 20 minutes execution
         */
        ini_set('max_execution_time', 1200);

        /**
         * start collecting the revisions
         */
        if($dtcnt = $revisionDates->count())
            $revise = $this->revise(
                $project,
                $revisionDates
            );

        return $this->respond([
            'message' => 'Load successfully .... '.$dtcnt.' estimations with projectDateRevisions at '.Carbon::parse(null, 'Europe/Helsinki'),
            'status' => 'success',
            'extra' => $dtcnt ? '' : 'covered'
        ]);
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
        $_date = Carbon::parse($date);

        $result = collect([]);
//        $commits = $project->commits()
//            ->select(
//                'id as CommitId',
//                'date_committed as Date',
//                'author_email as AuthorEmail'
//            )
//            ->where('date_committed', '<=', $date)
//            ->orderBy('Date', 'asc')
//            ->get()
//            ->transform(function ($item, $key){
//            return [
//                'CommitId' => $item->CommitId,
//                'Date' => str_replace(['T', 'Z'], [' ', ''], $item->Date),
//                'AuthorEmail' => $item->AuthorEmail
//            ];
//        });

//        dd(json_encode($commits[0]));
        $distinct = $project->vcsFileRevisions()
            ->select('Id', 'CommitId', 'AuthorEmail', 'FiletypeId', 'Extension', 'Date', 'Alias', 'status' )
            ->where('Date', '<=', $date) //hopefully laravel didn't do string comparison but allow sql do the job
            ->orderBy('Date', 'asc');
//            ->with('vcsFIleType');

        $vcs_revisions = $distinct->get();

        $committer = $vcs_revisions->where('AuthorEmail', $revisionDate->CommitterId);
        $result->developers = $vcs_revisions->unique('AuthorEmail')->count();

        $result->total_developers = $this->total_developers;
        $result->total_imp_developers = $this->total_imp_developers;
        $result->total_oo_developers = $this->total_oo_developers;
        $result->total_xml_developers = $this->total_xml_developers;
        $result->total_xsl_developers = $this->total_xsl_developers;

        $result->imperative = $vcs_revisions->whereIn('Extension', $this->dot_imps)->unique('CommitId')->count();
        $result->oo = $vcs_revisions->whereIn('Extension', $this->dot_oos)->unique('CommitId')->count();
        $result->xml = $vcs_revisions->whereIn('Extension', $this->dot_xmls)->unique('CommitId')->count();
        $result->xsl = $vcs_revisions->where('Extension', $this->dot_xsl)->unique('CommitId')->count();

        $result->committer_previous = $committer->whereDate('Date', '<', $date)->unique('CommitId')->count();
        $result->committer_previous_imp = $committer->whereDate('Date', '<', $date)->whereIn('Extension', $this->dot_imps)->unique('CommitId')->count();
        $result->committer_previous_oo = $committer->whereDate('Date', '<', $date)->whereIn('Extension', $this->dot_oos)->unique('CommitId')->count();
        $result->committer_previous_xml = $committer->whereDate('Date', '<', $date)->whereIn('Extension', $this->dot_xmls)->unique('CommitId')->count();
        $result->committer_previous_xsl = $committer->whereDate('Date', '<', $date)->where('Extension', $this->dot_xsl)->unique('CommitId')->count();

        $__files = [];
        $__files['imp_files']  = 0;
        $__files['oo_files']  = 0;
        $__files['xml_files']  = 0;
        $__files['xsl_files']  = 0;
        $__files['files']  = 0;

        $vcs_revisions->each(function ($vcs_fr) use (&$__files){
            if ($vcs_fr['status'] === 'added') {
                $__files['files'] ++;
                if (in_array($vcs_fr['Extension'], $this->dot_imps)){
                    $__files['imp_files'] ++;
                }
                if (in_array($vcs_fr['Extension'], $this->dot_oos)){
                    $__files['oo_files'] ++;
                }
                if (in_array($vcs_fr['Extension'], $this->dot_xmls)){
                    $__files['xml_files'] ++;
                }
                if ($vcs_fr['Extension'] === $this->dot_xsl){
                    $__files['xsl_files'] ++;
                }
            }
            if ($vcs_fr['status'] === 'removed') {
                $__files['files'] --;
                if (in_array($vcs_fr['Extension'], $this->dot_imps)){
                    $__files['imp_files'] --;
                }
                if (in_array($vcs_fr['Extension'], $this->dot_oos)){
                    $__files['oo_files'] --;
                }
                if (in_array($vcs_fr['Extension'], $this->dot_xmls)){
                    $__files['xml_files'] --;
                }
                if ($vcs_fr['Extension'] === $this->dot_xsl){
                    $__files['xsl_files'] --;
                }
            }

        });

        $result->files = $__files['files'];
        $result->imp_files = $__files['imp_files'];
        $result->oo_files = $__files['oo_files'];
        $result->xml_files = $__files['xml_files'];
        $result->xsl_files = $__files['xsl_files'];

        $result->imp_developers = $vcs_revisions->whereIn('Extension', $this->dot_imps)->unique('AuthorEmail')->count();
        $result->oo_developers =  $vcs_revisions->whereIn('Extension', $this->dot_oos)->unique('AuthorEmail')->count();
        $result->xml_developers = $vcs_revisions->whereIn('Extension', $this->dot_xmls)->unique('AuthorEmail')->count();
        $result->xsl_developers = $vcs_revisions->where('Extension', $this->dot_xsl)->unique('AuthorEmail')->count();

        $result->yearly_loc_churn = $project->commits()
            ->where('date_committed', '>', $date)
            ->where('date_committed', '<=', $_date->copy()->addYear()->toDateTimeString())
        ->sum('total');

        $_idx = $this->project_dates_index->search($date, true)+1;
        $_perc = ($_idx/$this->total_dates_count)*1;
        $result->dev_percent = $_perc;
//        dd(json_encode($this->toArray($files_todate)));
        return $result;
    }





    /*
     * Key: Project Date Revision Id
     * Predicted column (needed for training as well): Project Yearly LOC Churn
     */

    function revise($project, $revisionDates)
    {
        $revisionchunks = $revisionDates->chunk(50);
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
                $this->populateEstimations($date, 'Date', $date, 'normal');
                $this->populateEstimations($date, 'RevisionNumber', $this->project_commits_index->search($date, true)+1, 'normal');

                /**
                 * General counts
                 */

                $_counts = $this->countTillDate($project, $date, $revisionDate);

                $imperative_count = $_counts->imperative;
                $oo = $_counts->oo;
                $xml = $_counts->xml;
                $xsl = $_counts->xsl;

                $committer_previous = $_counts->committer_previous;
                $committer_previous_imp = $_counts->committer_previous_imp;
                $committer_previous_oo = $_counts->committer_previous_oo;
                $committer_previous_xml = $_counts->committer_previous_xml;
                $committer_previous_xsl = $_counts->committer_previous_xsl;

                $files = $_counts->files;
                $imp_files = $_counts->imp_files;
                $oo_files = $_counts->oo_files;
                $xml_files = $_counts->xml_files;
                $xsl_files = $_counts->xsl_files;

                $total_developers = $this->total_developers;
                $total_imp_developers = $this->total_imp_developers;
                $total_oo_developers = $this->total_oo_developers;
                $total_xml_developers = $this->total_xml_developers;
                $total_xsl_developers = $this->total_xsl_developers;

                $developers_to_date = $_counts->developers;
                $imp_developers_to_date = $_counts->imp_developers;
                $oo_developers_to_date = $_counts->oo_developers;
                $xml_developers_to_date = $_counts->xml_developers;
                $xsl_developers_to_date = $_counts->xsl_developers;

                $yearly_loc_churn = $_counts->yearly_loc_churn;
                $dev_percent = $_counts->dev_percent;

                /**
                 * Other derived fields
                 */
                $this->populateEstimations($date, 'Total_Developers', $total_developers, 'on');
                $this->populateEstimations($date, 'Total_Imp_Developers', $total_imp_developers, 'on');
                $this->populateEstimations($date, 'Total_OO_Developers', $total_oo_developers, 'on');
                $this->populateEstimations($date, 'Total_XML_Developers', $total_xml_developers, 'on');
                $this->populateEstimations($date, 'Total_XSL_Developers', $total_xsl_developers, 'on');

                $this->populateEstimations($date, 'Developers_On_Project_To_Date', $developers_to_date, 'on');
                $this->populateEstimations($date, 'Imp_Developers_On_Project_To_Date', $imp_developers_to_date, 'on');
                $this->populateEstimations($date, 'OO_Developers_On_Project_To_Date', $oo_developers_to_date, 'on');
                $this->populateEstimations($date, 'XML_Developers_On_Project_To_Date', $xml_developers_to_date, 'on');
                $this->populateEstimations($date, 'XSL_Developers_On_Project_To_Date', $xsl_developers_to_date, 'on');

                $this->populateEstimations($date, 'Avg_Previous_Imp_Commits', $imperative_count / $developers_to_date, 'on');
                $this->populateEstimations($date, 'Avg_Previous_OO_Commits', $oo / $developers_to_date, 'on');
                $this->populateEstimations($date, 'Avg_Previous_XML_Commits', $xml / $developers_to_date, 'on');
                $this->populateEstimations($date, 'Avg_Previous_XSL_Commits', $xsl / $developers_to_date, 'on');

                $this->populateEstimations($date, 'Committer_Previous_Commits', $committer_previous, 'on');
                $this->populateEstimations($date, 'Committer_Previous_Imp_Commits', $committer_previous_imp, 'on');
                $this->populateEstimations($date, 'Committer_Previous_OO_Commits', $committer_previous_oo, 'on');
                $this->populateEstimations($date, 'Committer_Previous_XML_Commits', $committer_previous_xml, 'on');
                $this->populateEstimations($date, 'Committer_Previous_XSL_Commits', $committer_previous_xsl, 'on');

                $this->populateEstimations($date, 'Files', $files, 'abc');
                $this->populateEstimations($date, 'Imperative_Files', $imp_files, 'abc');
                $this->populateEstimations($date, 'OO_Files', $oo_files, 'abc');
                $this->populateEstimations($date, 'XML_Files', $xml_files, 'abc');
                $this->populateEstimations($date, 'XSL_Files', $xsl_files, 'abc');

                $this->populateEstimations($date, 'ProjectYearlyLOCChurn', $yearly_loc_churn, 'abc');
                $this->populateEstimations($date, 'DevelopmentStageAsPercent', $dev_percent, 'abc');

//                $cylce++;
//                if ($cylce === 10): break; endif;
            }
//            dd(json_encode($this->estimations));

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


        return true;

    }

}