<?php

namespace App\Http\Controllers\Costs;

use App\ChurnCostsModels\ModuleChurnLevel;
use App\CommitsFileChange;
use App\Developer;
use App\Folder;
use App\Http\Controllers\Controller;
use App\Issue;
use App\Project;
use App\VCSModels\VCS_Module;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function PHPSTORM_META\type;

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
//      Update the comparison of a variable
//        DB::statement("
//                        update projectcostdifference
//                        set
//                          `fixesCompare`
//                            = CASE WHEN (`fixesDifference` > 4)
//                                THEN IF(`fixes` > `fixes2`, 1, -1)
//                              ELSE 0 END"
//        );

        //Update the difference of a variable
//        DB::statement("
//                        update projectcostdifference
//                        set
//                          `locDifference`= ABS(loc1 - loc2)"
//        );

        //Update the compare of a variable
//        DB::statement("
//                        update projectcostdifference
//                        set `locCompare`
//                          = CASE WHEN (`locDifference` > 4) #8038 90%
//                                THEN IF(`loc1` > `loc2`, 1, -1)
//                              ELSE 0 END"
//        );
        $this->validate($request, [
            'level' => 'required|in:3,4'
        ]);
        $project_id = 6;
        $module_level = 4;

        $result = DB::table('projectmoduleachurnhistory')
            ->where([
                'ProjectId' => $project_id,
                'ModuleLevel' => $module_level,
                'taken' => false
            ])->take(100)
            ->pluck('Id');

        DB::table('projectmoduleachurnhistory as t1')
            ->join('projectmoduleachurnhistory as t2', function ($join) {
                $join->on('t1.Date', '=', 't2.Date')
                    ->on('t1.ProjectId', '=', 't2.ProjectId')
                    ->on('t1.ModulePath', '<>', 't2.ModulePath')
                    ->on('t1.ModulePath', 'NOT LIKE', DB::raw('CONCAT(`t2`.`ModulePath`, "%")'))
                    ->on('t2.ModulePath', 'NOT LIKE', DB::raw('CONCAT(`t1`.`ModulePath`, "%")'));
        })
        ->selectRaw('
          t1.Id,
          t1.ProjectId,
          t1.ModuleLevel,
          t1.ModulePath,
          t2.ModulePath                                AS ModulePath2,
          t1.AlternativeCost,
          t2.AlternativeCost                           as AlternativeCost2,
          ABS(t1.AlternativeCost - t2.AlternativeCost) AS costDifference,
          CASE WHEN (ABS(t1.AlternativeCost - t2.AlternativeCost) > 8000)
            THEN IF(t1.AlternativeCost > t2.AlternativeCost, 1, -1)
          ELSE 0 END                                   as costCompare,
          ABS(t1.fixes - t2.fixes)                     AS fixesDifference,
          CASE WHEN (ABS(t1.fixes - t2.fixes) > 8)
            THEN IF(t1.fixes > t2.fixes, 1, -1)
          ELSE 0 END                                   as fixesCompare,
          t1.loc                                       AS loc1,
          t2.loc                                       AS loc2,
          t1.fixes,
          t2.fixes                                     as fixes2,
          t1.Date
        ')->whereIn('t1.Id', $result)
            ->get()
            ->each(function ($chunk) {
                DB::table('projectcostdifference')->insert([
                  'ProjectId' =>  $chunk->ProjectId,
                  'ModuleLevel' =>  $chunk->ModuleLevel,
                  'ModulePath' =>  $chunk->ModulePath,
                  'ModulePath2' =>  $chunk->ModulePath2,
                  'AlternativeCost' =>  $chunk->AlternativeCost,
                  'AlternativeCost2' =>  $chunk->AlternativeCost2,
                  'costDifference' =>  $chunk->costDifference,
                  'costCompare' =>  $chunk->costCompare,
                  'fixesDifference' =>  $chunk->fixesDifference,
                  'fixesCompare' =>  $chunk->fixesCompare,
                  'loc1' =>  $chunk->loc1,
                  'loc2' =>  $chunk->loc2,
                  'fixes' =>  $chunk->fixes,
                  'fixes2' =>  $chunk->fixes2,
                  'Date' =>  $chunk->Date,
                ]);
        });

        $result_count = count($result);
        if ($result_count > 0) {

            DB::table('projectmoduleachurnhistory')->whereIn('Id', $result)
                ->update(['taken' => true]);

            return [
              'status' => 'success',
                'extra' => '',
                'message' => 'successfully loaded cost diffs of '.$result_count
            ];
        }

        return [
            'status' => 'success',
            'extra' => 'covered',
            'message' => 'Finished loading cost diffs left: '.$result_count
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
