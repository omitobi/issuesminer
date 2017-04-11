<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 17:32
 */

namespace App\Http\Controllers\VCS;


use App\Utilities\Utility;
use App\VCSModels\VCSExtension;
use App\VCSModels\VCSProject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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


    public function sortExtensions(Request $request)
    {
        $project_name = $request->project_name;

        $oo_langs = ['c', 'h', 'cpp', 'cs', 'php', 'java', 'cxx', 'hpp','js'];
        $imp_langs = ['cpp', 'cs', 'php', 'java', 'cxx', 'hpp', 'js'];
        $texts = ['dtd', 'py', 'php', 'java', 'rb', 'sgml', 'txt', 'wsdl', 'xsd'];

        $vcs_project = VCSProject::where('Name', $project_name)->first();

        $_vfiles = $vcs_project->VCSFiles;

        $extensions = [];

        Model::unguard();
        foreach ($_vfiles as $chunk)
        {
            $ext = pathinfo($chunk['Name'], PATHINFO_EXTENSION);

            $vcs_extension = new VCSExtension();
            $vcs_extension->Extension  = ".".$ext;
            $vcs_extension->Type  = mb_strtoupper($ext);


//            if(in_array($ext, $oo_langs)){
//                $vcs_extension->isOO = true;
//            }
//            if(in_array($ext, $imp_langs)){
//                $vcs_extension->isImperative = true;
//            }
            if($ext === 'xml' || $ext === 'xsd' || $ext === 'wsdl'){
                $vcs_extension->isXML  = true;
            }

            if(in_array($ext, $texts)){
                $vcs_extension->isText  = true;
            }

            if(!VCSExtension::where('Extension', ".".$ext)->first()) {
                $extensions[] = $vcs_extension->saveOrFail();
            } else $extensions[] = $vcs_extension->update();

        }
        Model::reguard();
        return $extensions;
    }
}