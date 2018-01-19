<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 16/01/2018
 * Time: 01:57
 */

namespace App\Http\Controllers\Costs;


use App\ChurnCostsModels\ModuleChurnLevel;
use App\Project;
use App\Utilities\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LOCCounterController extends Utility
{
    public function load(Request $request, $project_id)
    {
//            $list_count_at_date = $this->runProcess('cat listOfFiles | xargs wc -l', $cwd.$module_);
        $request->merge(['project_id' => $project_id]);

        $this->validate($request, [
            'project_id' => 'required|min:1|in:1,4,6,9|exists:projects,id',
            'module_level' => 'required|int|min:1|max:4',
        ]);

        $server_listOfFiles = '/Applications/MAMP/htdocs/listOfFiles';

        $project = Project::findOrFail($project_id);

        /**
         * Sample:
         *
         * //changes jquery/build/
         * //changes 2013-01-08
         */
        $module_level = $request->get('module_level');
        $locs = [];
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

            Log::info('Loading LOC For project: '.$cost->ProjectId.'\'s '.$cost->Date.' for plus_year '.$date_plus_year_->toDateString().' and module: '.$cost->ModulePath.' at Level: '.$cost->ModuleLevel, $_dt);

            $cwd = "/Applications/MAMP/htdocs/projects_src/$project->name";

            $module = $cost->ModulePath;
            $module_ = Str::replaceFirst($project->name,'', $module );

            $result = collect();

            $result->push($this->runProcess('git rev-list -1 --before="'.$date_plus_year_->copy()->addDay()->startOfDay()->toDateString().'" master', $cwd));
            $sha = $result->first()->get('result')->first();

            $loc_at_date = 0;

            if (! is_null($sha)) {
                $this->runProcess('git checkout '.$sha, $cwd);

                if (! file_exists($cwd.$module_)) {
                    // skip updating if module does not exist after checkout
                    Log::notice("Skip updating since module '$cwd$module_' does not exist after checkout of sha: $sha -- will checkout to master branch");
                } else {
                    $this->runProcess('git ls-files > '.$server_listOfFiles, $cwd.$module_);

                    $list_count_at_date = $this->runProcess("cat $server_listOfFiles | line", $cwd.$module_);
                    $result->push($list_count_at_date);
                    $count_string_at_date = $list_count_at_date->get('result')->last();
                    $loc_at_date = Str::startsWith($count_string_at_date, 'line count:') ? intval(trim(Str::replaceLast('line count:', '', $count_string_at_date))) : $loc_at_date;
                    $result->push(['loc' => $loc_at_date]);
                }

                $result->push($this->runProcess('git checkout master', $cwd)); //checkout back to master

                if ($loc_at_date > 0) {
                    $cost->loc = $loc_at_date;
                    $cost->update();
                    $locs[$cost->ModulePath.'|'.$cost->Date] = $loc_at_date;
                    Log::info('DONE: Successfully checked out to master branch of '.$project->name.' after saving loc: '.$loc_at_date, [$_dt, $result->toArray()]);
                } else {
                    Log::notice('SKIPPED: Successfully checked out to master branch of '.$project->name.' for loc: '.$loc_at_date, [$_dt, $result->toArray()]);
                }

            }

        }

        return $locs;

    }

    public function runProcess($command, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = array())
    {
        $process = new Process($command, $cwd, $env, $input, $timeout, $options);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return collect([
//            '_result' => $process->getOutput(),
//            'trimmed_result' => trim($process->getOutput()),
            'command' => $process->getCommandLine(),
            'result' => collect(array_values(array_filter(explode("\n", str_replace(["\t", "\""], ['','\''], trim($process->getOutput())))))),
        ]);
    }
}