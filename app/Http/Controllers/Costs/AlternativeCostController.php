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

        $last_date = '2017-03-31';
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
            Log::info('Loading '.$cost->ProjectId.'\'s '.$cost->Date.' and module: '.$cost->ModulePath.' at Level: '.$cost->ModuleLevel);

            $date_plus_year = Carbon::parse($cost->Date)->addYear();
            $issues_ = Issue::where('project_id', $project_id)
                ->whereDate('date_closed', '>=', $cost->Date)
                ->whereDate('date_created', '<=', $date_plus_year)
                ->whereDate('date_created', '<=', $last_date) //todo: seems there's a wrong query here.
                ->whereDate('date_closed', '<=', $last_date)
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
                Log::info('DONE: Updating '.$cost->ProjectId.'\'s '.$cost->Date.' and module: '.$cost->ModulePath.' at Level: '.$cost->ModuleLevel. ' Fixes: '.$cost->fixes);
            } else {
                Log::info('SKIPPED: Updating '.$cost->ProjectId.'\'s '.$cost->Date.' and module: '.$cost->ModulePath.' at Level: '.$cost->ModuleLevel. ' Fixes: '.$cost->fixes);
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
