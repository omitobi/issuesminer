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
use App\VCSModels\VCSFiletype;
use App\VCSModels\VCSProject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class VCSFilesController extends Utility
{

    public function save(Request $request)
    {
        $vcsproject = VCSProject::find($request->id);
        if(!$vcsproject){
            return $this->respond('Project does not exist', 404);
        }
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
        $project_name = $request->get('project_name');
        $vcs_project = null;
        if($request->get('pid')){
            $vcs_project = VCSProject::find($request->get('pid'));
        }

        if(!$vcs_project){
            $vcs_project  = VCSProject::where('Name', $project_name)->first();
        }
        if(!$vcs_project){
            return $this->respond('Project does not exist', 404);
        }

        $_vfiles = $vcs_project->VCSFiles;

        $extensions = [];

        Model::unguard();
        foreach ($_vfiles as $chunk)
        {
            $ext = pathinfo($chunk['Name'], PATHINFO_EXTENSION);

            $vcs_extension = new VCSExtension();
            $vcs_extension->Extension  = ".".$ext;
            $_type = (isset($this->types_[mb_strtolower($ext)])) ? $this->types_[mb_strtolower($ext)] : mb_strtoupper($ext);
            $vcs_extension->Type  = $_type;


//            if(in_array($ext, $oo_langs)){
//                $vcs_extension->isOO = true;
//            }
//            if(in_array($ext, $imp_langs)){
//                $vcs_extension->isImperative = true;
//            }
            if($ext === 'xml' || $ext === 'xsd' || $ext === 'wsdl' || $ext === 'xsl'){
                $vcs_extension->isXML  = true;
            }

            if(in_array($ext, $this->texts)){
                $vcs_extension->isText  = true;
            }

            if(!VCSExtension::where('Extension', ".".$ext)->first()) {
                $extensions[] = $vcs_extension->saveOrFail();
            } else $extensions[] = $vcs_extension->update();

        }
        Model::reguard();
        return $extensions;
    }


    public function sortFileTypes(Request $request)
    {
        $project_name = $request->get('project_name');
        $vcs_project = null;
        if($request->get('pid')){
            $vcs_project = VCSProject::find($request->get('pid'));
        }

        if(!$vcs_project){
            $vcs_project  = VCSProject::where('Name', $project_name)->first();
        }
        if(!$vcs_project){
            return $this->respond('Project does not exist', 404);
        }

        $_vfiles = $vcs_project->VCSFiles;

        $extensions = [];

        Model::unguard();
        foreach ($_vfiles as $chunk)
        {
            $ext = pathinfo($chunk['Name'], PATHINFO_EXTENSION);

            $vcs_file_types = new VCSFiletype();
            $_type = (isset($this->types_[mb_strtolower($ext)])) ? $this->types_[mb_strtolower($ext)] : mb_strtoupper($ext);
            $vcs_file_types->Type  = $_type;


            if(in_array($ext, $this->oo_langs)){
                $vcs_file_types->isOO = true;
            }
            if(in_array($ext, $this->imp_langs)){
                $vcs_file_types->isImperative = true;
            }

            if($ext === 'xml' || $ext === 'xsd' || $ext === 'wsdl' || $ext === 'xsl'){
                $vcs_file_types->isXML  = true;
            }

            if(in_array($ext, $this->texts)){
                $vcs_file_types->isText  = true;
            }

            if(!VCSFiletype::where('Type', $_type)->first()) {
                $extensions[] = $vcs_file_types->saveOrFail();
            } else $extensions[] = $vcs_file_types->update();

        }
        Model::reguard();
        return $extensions;
    }

}