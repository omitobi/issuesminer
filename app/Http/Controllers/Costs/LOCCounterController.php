<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 16/01/2018
 * Time: 01:57
 */

namespace App\Http\Controllers\Costs;


use App\Project;
use App\Utilities\Utility;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class LOCCounterController extends Utility
{
    public function load(Request $request, $project_id)
    {
        $request->merge(['project_id' => $project_id]);

        $this->validate($request, [
            'project_id' => 'required|exists:projects,id'
        ]);

        $server_listOfFiles = '/Applications/MAMP/htdocs/listOfFiles';

        $project = Project::findOrFail($project_id);
        $cwd = '/Applications/MAMP/htdocs/digipesu';

        $module = 'jquery/app/Http/Controllers1';
        $module_ = Str::replaceFirst($project->name,'', $module );

        $result = collect();

        $result->push($this->runProcess('git rev-list -1 --before="2017-01-15 12:00" master', $cwd));
        $sha = $result->first()->get('result')->first();

        $loc_at_date = 0;
        if (! is_null($sha)) {
            $result->push($this->runProcess('git checkout '.$sha, $cwd));
            $result->push($this->runProcess('git ls-files > '.$server_listOfFiles, $cwd.$module_));
//            $list_count_at_date = $this->runProcess('cat listOfFiles | xargs wc -l', $cwd.$module_);
            $list_count_at_date = $this->runProcess("cat $server_listOfFiles | line -d", $cwd.$module_);
            $result->push($list_count_at_date);
//
            $count_string_at_date = $list_count_at_date->get('result')->last();

            $loc_at_date = Str::startsWith($count_string_at_date, 'line count:') ? intval(trim(Str::replaceLast('line count:', '', $count_string_at_date))) : $loc_at_date;
            $result->push(['loc' => $loc_at_date]);

            /**
             * Update the db column to keep this LOC
             */

            $result->push($this->runProcess('git checkout master', $cwd)); //checkout back to master

        }

        return $result ?: 'No output';
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