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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlternativeCostController extends Controller
{

    /**
     * @param Request $request ['module_level': {1..4}]
     * @param $project_id
     * @return array|mixed
     */
    public function mergeCostsAndIssues(Request $request, $project_id)
    {
        $request->merge(['project_id' => $project_id]);

        $this->validate($request, [
            'project_id' => 'required|min:1|in:1,4,6,9|exists:projects,id',
            'module_level' => 'required|int|min:1|max:4',
        ]);

        /**
         * Sample:
         *
         * //changes jquery/build/
         * //changes 2013-01-08
         */
        $module_level = $request->get('module_level');
        $issues = [];
        $last_date = Carbon::parse('2017-03-31');

        foreach (ModuleChurnLevel::whereDate('Date', '<=', $last_date)
                     ->where('ProjectId', $project_id)
                     ->where('ModuleLevel', $module_level)
                     ->orderBy('Date')
                     ->cursor() as $cost)
        {

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
                    $file_change->where('module', 'LIKE', $cost->ModulePath.'%');
                })->count();

            if( $issues_ > 0 ) {
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

    public function getCostDiffs(Request $request)
    {
        $this->validate($request, [
            'level' => 'required|in:4'
        ]);
        $project_id = 6;
        $module_level = 4;


            DB::table('projectmoduleachurnhistory as t1')
            ->join('projectmoduleachurnhistory as t2', function ($join) {
                $join->on('t1.Date', '=', 't2.Date')
                    ->on('t1.ProjectId', '=', 't2.ProjectId')
                    ->on('t1.ModulePath', '<>', 't2.ModulePath')
                    ->on('t1.ModulePath', 'NOT LIKE', DB::raw('CONCAT(`t2`.`ModulePath`, "%")'))
                    ->on('t2.ModulePath', 'NOT LIKE', DB::raw('CONCAT(`t1`.`ModulePath`, "%")'));
            })
            ->selectRaw('
            t1.ProjectId, 
            t1.ModuleLevel, 
            t1.ModulePath,
            t2.ModulePath as ModulePath2,
            ABS(t1.AlternativeCost - t2.AlternativeCost) as costDifference,
            t1.loc as loc1,
            t2.loc as loc,
            t1.Date
        ')->where([
                't1.ProjectId' => $project_id,
                't1.ModuleLevel' => $module_level
            ])->orderBy('t1.Date')->chunk(5000, function ($chunks) {
               DB::table('projectcostdiffs')->insert($chunks);
            });


        return [
            $project_id,
            $module_level,
        ];
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
