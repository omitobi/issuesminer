<?php

namespace App\Http\Controllers\Costs;

use App\ChurnCostsModels\ModuleChurnLevel;
use App\CommitsFileChange;
use App\Http\Controllers\Controller;
use App\Issue;
use App\Project;
use App\VCSModels\VCS_Module;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlternativeCostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //pp
    }

    /**
     * @param Request $request ['module_level': {1..4}]
     * @param $project_id
     * @return array
     */
    public function mergeCostsAndIssues(Request $request, $project_id)
    {
        $this->validate($request, [
            'module_level' => 'required|int|min:1|max:4'
        ]);

        $project = Project::findOrFail($project_id);
        /**
         * Sample:
         *
         * //changes jquery/build/
         * //changes 2013-01-08
         */
        $module_level = $request->get('module_level');
        $count = 0;
        $issues = [];
        $affected_modules = CommitsFileChange::whereProjectId($project_id)
            ->whereRaw('LENGTH(module) - LENGTH(REPLACE(module, \'/\', \'\')) = '.$module_level)
            ->distinct('module')->pluck('module');

        $last_date = Carbon::parse('2017-03-31');
//        return ['affected_modules' => $affected_modules,
//            'ModulePath' => ModuleChurnLevel::where('ProjectId', $project_id)->whereDate('Date', '<=', $last_date)->where('ModuleLevel', $module_level)
//                ->whereIn('ModulePath', $affected_modules)->distinct()->pluck('ModulePath')->intersect($affected_modules)
//            ];
//        return ModuleChurnLevel::whereDate('Date', '<=', $last_date)->where('ModuleLevel', $module_level)
//            ->whereIn('ModulePath', $affected_modules)->get()->filter(function ($churn) use ($project){
//                return ! starts_with($churn->ModulePath, $project->name);
//            });

        foreach (ModuleChurnLevel::whereDate('Date', '<=', $last_date)->where('ModuleLevel', $module_level)
                     ->whereIn('ModulePath', $affected_modules)->orderBy('Date')->cursor() as $cost) {

            ini_set('max_execution_time', 1200);

            $date_plus_year = Carbon::parse($cost->Date)->addYear();
            $date_plus_year_= $date_plus_year->greaterThan($last_date) ? $last_date : $date_plus_year;

            $_dt['md'] = $cost->ModulePath;
            $_dt['cd'] = $cost->Date;
            $_dt['d+y'] = $date_plus_year->toDateString();
            $_dt['d+y_'] = $date_plus_year_->toDateString();
            $_dt['ld'] = $last_date->toDateString();

            Log::info('Loading '.$cost->ProjectId.'\'s '.$cost->Date.' and module: '.$cost->ModulePath.' at Level: '.$cost->ModuleLevel, $_dt);

            $issues_ = Issue::where('project_id', $project_id)
                ->whereDate('date_created', '<=', $date_plus_year_->toDateString())
                ->whereDate('date_closed', '>=', $cost->Date)
                ->whereHas('fileChanges', function ($file_change) use ($cost){
                    $file_change->where('module', $cost->ModulePath);
                })->count();


            if (! starts_with($cost->ModulePath, $project->name)) {
                continue;
            }

            if( $issues_ ) {
                $cost->fixes = $issues_;
                $cost->update();
                $issues[$cost->ModulePath.'|'.$cost->Date] = $issues_;
                Log::info('DONE: Updating '.$cost->ProjectId.'\'s '.$cost->Date.' and module: '.$cost->ModulePath.' at Level: '.$cost->ModuleLevel. ' Fixes: '.$cost->fixes, $_dt);
            } else {
                Log::info('SKIPPED: Updating '.$cost->ProjectId.'\'s '.$cost->Date.' and module: '.$cost->ModulePath.' at Level: '.$cost->ModuleLevel. ' Fixes: '.$cost->fixes, $_dt);
            }

        }
        return $issues;

    }


    /*-- SELECT COUNT(*) from (
    SELECT
    -- cfc.commit_id,
    -- COUNT(distinct(cfc.module)) as bug_freq,
    cfc.issue_id,
    cfc.module,
    DATE_FORMAT(i.date_closed, '%Y-%m-%d') AS Date
    -- count(distinct cfc.module)
    FROM
    commits_file_changes cfc,
    issues i
    WHERE
    LENGTH(cfc.module) - LENGTH(REPLACE(cfc.module, '/', '')) = 2
    AND cfc.project_id = 1
    AND cfc.issue_id = i.id
    AND (i.date_closed >= '2013-01-08' and i.date_created <= '2014-01-08')
    AND cfc.module = 'jquery/src/'
    GROUP BY  Date, cfc.module, cfc.issue_id;
    -- ORDER BY Date) as nt;*/
}
