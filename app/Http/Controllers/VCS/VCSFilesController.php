<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 17:32
 */

namespace App\Http\Controllers\VCS;


use App\Utilities\Utility;
use App\VCSModels\VCSProject;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VCSFilesController extends Utility
{

    public function save(Request $request)
    {
        $vcsproject = VCSProject::find($request->id);
//        return $vcsproject;
//        $p_name = 'jquery';
        $_files = [];
            $projects = $this->jsonToArray($this->getContents('paths/'.$vcsproject->Name.'.json'));
            $_path = collect();
            foreach ($projects as $project)
            {
                $path = substr($project, strpos($project,'Project_source/'.$vcsproject->Name)+15+strlen($vcsproject->Name)+1 );
                $_path->push(['name' => $path, 'ProjectId' => $vcsproject->Id,
                    'created_at' => Carbon::now()->toDateTimeString(), 'updated_at' => Carbon::now()->toDateTimeString()]);
            }

            foreach ($_path->chunk(300)->toArray() as $chunk)
            {
                $_files[] = $vcsproject->VCSFiles()->insert($chunk);
            }


//        return $this->respond($project, 200, true);

        return $_files;
    }
}