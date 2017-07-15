<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 14/07/2017
 * Time: 23:11
 */

namespace App\Http\Controllers\VCS;


use App\Http\Controllers\Controller;
use App\Project;
use App\Utilities\Util;
use App\VCSModels\VCS_Module;
use App\VCSModels\VCSProject;

class TestersController extends Controller
{
    use Util;
    public function updateVCS_ModulesWithDate($project_id)
    {
        return ;
        $project = $this->getProject($project_id);
        $vcs_modules = [];

        foreach ($project->projectDateRevisions()
                     ->select('Id as ProjectDateRevisionId', 'Date', 'module_touched')
                     ->cursor()  as $date) {
            $vcs_modules[] = $project->vcsModules()
                 ->where('ProjectDateRevisionId','=', $date->ProjectDateRevisionId)
                ->update(['Date' => $date->Date]);
        }

//         foreach ($dates_with_id as $date) {
//             $vcs_modules[] = $project->vcsModules()
//                 ->where('ProjectDateRevisionId','!=', $date->ProjectDateRevisionId)
//                 ->get();
////             dd($date->ProjectDateRevisionId);
////             break;
//         }
//        $project_date_with_id->each( function ($value, $key) use ($vcs_modules){
//
//        });
        return $vcs_modules;
    }
}