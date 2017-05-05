<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:48
 */
namespace App\Http\Controllers\VCS;


use App\Project;
use  \App\Utilities\Utility;
use App\VCSModels\ProjectDateRevision;
use App\VCSModels\VCSProject;
use App\VCSModels\VCSSystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class VCSModulesController extends Utility
{

    protected $modules;
    protected $premodules;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function loadAll(Request $request)
    {
        /**
         * Allow up to 5 minutes execution
         */
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '256M');
//        ini_set('php_value memory_limit', '256M');

        if(!$_project = $request->get('project_name')) {
            return response(['error' => 'invalid project_name'], 400);
        }
//         sleep ( 61 );
        if(!$project = VCSProject::where('name', $_project)
            ->orWhere('id', $_project)->first()) {
            return $this->respond('Project does not exist', 404);
        }

        $date_revisions = $project->projectDateRevisions()->where('module_touched', '0')->take(50)->get();
        $modules = $this->modulate(
                $project,
            $date_revisions
        );

        $rev_count = $date_revisions->count();
        return $this->respond([
            'message' => 'Load successfully .... VCSModules with '.$rev_count.' VCSProjectDateRevisions',
            'status' => 'success',
            'extra' => $rev_count ? '' : 'covered'
        ]);
        return $this->respond(['passed']);
    }

    /**
     * @param $paths
     * @param bool $repeat
     * @return array
     */
    function getModules($paths, $repeat = false)
    {
        $result = [];

        if(!is_string($paths)) {
            foreach ($paths as $path)
            {
                if( !$last_slash = strrpos( $path, '/' )){
                    $result[] = '';
                } else{
                    $result[] = substr($path, 0,  $last_slash+1);
                }
            }
            return array_values(array_unique($result));
        }

        if( !$last_slash = strrpos( $paths, '/' )){
            return [''];
        }

        $result[] = substr($paths, 0,  $last_slash+1);
        return $result;
    }

    /**
     * @param $project
     * @param $revisionDate
     * @return \Illuminate\Support\Collection
     */
    function countTillDate($project, $revisionDate)
    {
        $result = collect([]);

        $distinct = $project->vcsFileRevisions()->select('Date', 'Alias', 'Extension')
            ->where('Date', '<=', $revisionDate->Date)//hopefully laravel didn't do string comparison but allow sql do the job
            ->orderBy('Date', 'asc')->groupBy('Alias');
        $vcs_revisions = $distinct->get();  //todo: why not return distinct result already from query?

//        dd(json_encode($vcs_revisions->pluck('Alias')->values()));
        $all_files = $vcs_revisions;

        $modules = [];

        $result->the_modules = $this->getModules($all_files->pluck('Alias')->values());

        $result->modules_files = $all_files;


        foreach ($result->the_modules as $module)
        {
            $all_the_files = 0;
            $xml_here = 0;
            $imp_here = 0;
            $oo_here = 0;
            $xls_here = 0;
            $java_here = 0;
            $cpp_here = 0;
            $c_here = 0;
            $cs_here = 0;
            $rb_here = 0;
            $php_here = 0;
            $js_here = 0;

            foreach ($result->modules_files as $modules_file)
            {
                $extension = mb_strtolower(substr($modules_file->Extension, 1));

                if(starts_with($modules_file->Alias, $module)){
                    $all_the_files++;
                    $modules[$module] = ['Files' => $all_the_files];


                    if(!isset($modules[$module])) {
                        if (in_array($extension, $this->xmls)) {
                            $xml_here++;
                            $modules[$module] = ['XMLFiles' => $xml_here];
                        }else{
                            $modules[$module] = ['XMLFiles' => $xml_here];
                        }
                        if (in_array($extension, $this->imp_langs)) {
                            $imp_here++;
                            $modules[$module] = ['ImperativeFiles' => $imp_here];
                        }else{
                            $modules[$module] = ['ImperativeFiles' => $imp_here];
                        }
                        if (in_array($extension, $this->oo_langs)) {
                            $oo_here++;
                            $modules[$module] = ['OOFiles' => $oo_here];
                        }else{
                            $modules[$module] = ['OOFiles' => $oo_here];
                        }
                        if ($extension === 'xsl') {
                            $xls_here++;
                            //todo: change table column name to XSLFiles and this to XSLFiles not 'XLS'
                            $modules[$module] = ['XLSFiles' => $xls_here];
                        }else{
                            $modules[$module] = ['XLSFiles' => $xls_here];
                        }
                        if ($extension === 'java') {
                            $xls_here++;
                            $modules[$module] = ['JAVAFiles' => $java_here];
                        }else{
                            $modules[$module] = ['JAVAFiles' => $java_here];
                        }
                        if (in_array(mb_strtolower($extension), ['c','h'])) {
                            $c_here++;
                            $modules[$module] = ['CFiles' => $c_here];
                        }else{
                            $modules[$module] = ['CFiles' => $c_here];
                        }
                        if (mb_strtolower($extension) === 'cs') {
                            $cs_here++;
                            $modules[$module] = ['CSharpFiles' => $cs_here];
                        }else{
                            $modules[$module] = ['CSharpFiles' => $cs_here];
                        }
                        if (mb_strtolower($extension) === 'rb') {
                            $rb_here++;
                            $modules[$module] = ['RubyFiles' => $rb_here];
                        }else{
                            $modules[$module] = ['RubyFiles' => $rb_here];
                        }
                        if (in_array(mb_strtolower($extension), ['php', 'phpt'])) {
                            $php_here++;
                            $modules[$module] = ['PHPFiles' => $php_here];
                        }else{
                            $modules[$module] = ['PHPFiles' => $php_here];
                        }
                        if (mb_strtolower($extension) === 'js') {
                            $js_here++;
                            $modules[$module] = ['JavaScriptFiles' => $js_here];
                        }else{
                            $modules[$module] = ['JavaScriptFiles' => $js_here];
                        }
                    }
                    else {
                        if (in_array($extension, $this->xmls)) {
                            $xml_here++;
                            $modules[$module]['XMLFiles'] = $xml_here;
                        }else{
                            $modules[$module]['XMLFiles'] = $xml_here;
                        }
                        if ($extension === 'xsl') {
                            $xls_here++;
                            $modules[$module]['XLSFiles'] =  $xls_here;
                        }else{
                            $modules[$module]['XLSFiles'] =  $xls_here;
                        }
                        if (in_array($extension, $this->oo_langs)) {
                            $oo_here++;
                            $modules[$module]['OOFiles'] = $oo_here;
                        }else{
                            $modules[$module]['OOFiles'] = $oo_here;
                        }
                        if (in_array($extension, $this->imp_langs)) {
                            $imp_here++;
                            $modules[$module]['ImperativeFiles'] =  $imp_here;
                        }else{
                            $modules[$module]['ImperativeFiles'] = $imp_here;
                        }
                        if ($extension === 'java') {
                            $xls_here++;
                            $modules[$module]['JavaFiles'] = $java_here;
                        }else{
                            $modules[$module]['JavaFiles'] = $java_here;
                        }
                        if (mb_strtolower($extension) === 'cpp') {
                            $cpp_here++;
                            $modules[$module]['CPPFiles'] = $cpp_here;
                        }else{
                            $modules[$module]['CPPFiles'] = $cpp_here;
                        }
                        if (in_array(mb_strtolower($extension), ['c','h'])) {
                            $c_here++;
                            $modules[$module]['CFiles'] = $c_here;
                        }else{
                            $modules[$module]['CFiles'] = $c_here;
                        }
                        if (mb_strtolower($extension) === 'cs') {
                            $cs_here++;
                            $modules[$module]['CSharpFiles'] = $cs_here;
                        }else{
                            $modules[$module]['CSharpFiles'] = $cs_here;
                        }
                        if (in_array(mb_strtolower($extension), ['php', 'phpt'])) {
                            $php_here++;
                            $modules[$module]['PHPFiles'] = $php_here;
                        }else{
                            $modules[$module]['PHPFiles'] = $php_here;
                        }
                        if (mb_strtolower($extension) === 'js') {
                            $js_here++;
                            $modules[$module]['JavaScriptFiles'] = $js_here;
                        }else{
                            $modules[$module]['JavaScriptFiles'] = $js_here;
                        }
                        if (mb_strtolower($extension) === 'rb') {
                            $rb_here++;
                            $modules[$module]['RubyFiles'] = $rb_here;
                        }else{
                            $modules[$module]['RubyFiles'] = $rb_here;
                        }
                    }
                    $modules[$module]['ModulePath'] = $project->Name.'/'.$module;
                }

            }
        }
        $result->modules = $modules;

        return $result;
    }

    public function modulate($project, $date_revisions)
    {
        $revisionchunks = $date_revisions->chunk(25);
        foreach ($revisionchunks as $chunk) {
//            $cycle = 0;
            foreach ($chunk as $revisionDate) {

                /**
                 * General counts
                 */

                $_counts = $this->countTillDate($project, $revisionDate);
                $modules = $_counts->modules;

                foreach ($modules as $p => $module) {

                    /**
                     * Others follow here
                     */

                    $this->populateModules($module['ModulePath'], 'ProjectDateRevisionId', $revisionDate->Id);
                    $this->populateModules($module['ModulePath'], 'ProjectId', $project->Id);
                    $this->populateModules($module['ModulePath'], 'CommitId', $revisionDate->CommitId);

                    /**
                     * Bringing them together
                     */

                    $this->populateModules($module['ModulePath'], 'Files', $module['Files']);
                    $this->populateModules($module['ModulePath'], 'ModulePath', $module['ModulePath']);

                    $this->populateModules($module['ModulePath'], 'XMLFiles', $module['XMLFiles']);
                    $this->populateModules($module['ModulePath'], 'XLSFiles', $module['XLSFiles']);
                    $this->populateModules($module['ModulePath'], 'ImperativeFiles', $module['ImperativeFiles']);
                    $this->populateModules($module['ModulePath'], 'OOFiles', $module['OOFiles']);
                    $this->populateModules($module['ModulePath'], 'JavaFiles', $module['JavaFiles']);
                    $this->populateModules($module['ModulePath'], 'CPPFiles', $module['CPPFiles']);
                    $this->populateModules($module['ModulePath'], 'CFiles', $module['CFiles']);
                    $this->populateModules($module['ModulePath'], 'CSharpFiles', $module['CSharpFiles']);
                    $this->populateModules($module['ModulePath'], 'PHPFiles', $module['PHPFiles']);
                    $this->populateModules($module['ModulePath'], 'JavaScriptFiles',$module['JavaScriptFiles']);
                    $this->populateModules($module['ModulePath'], 'RubyFiles', $module['RubyFiles']);

                    $this->populateModules($module['ModulePath'], 'ModuleId', sha1($module['ModulePath']));
                }
                /**
                 * Set and reset
                 */

                if($this->insertOrUpdate($this->premodules, 'VCS_modules')){
                    ProjectDateRevision::where(
                        'Id', $revisionDate->Id
                    )->where([
                        'ProjectId' => $revisionDate->ProjectId,
                        'module_touched' => '0'
                    ])->update([
                        'module_touched' => '1'
                    ]);
                }
//                $this->populateModules(
//                    null, $revisionDate->Id, $this->premodules, 'modules');
                unset( $this->premodules );
                $this->premodules = [];

            }

        }

        return true;
    }














































    function populateModules( $date, $field, $value, $modules='premodules', $more = false )
    {

        if(!$this->{$modules}) {
            $this->{$modules} = [];
        }
        if(!isset($this->{$modules}[$date])){

            $this->{$modules}[$date] = [$field => $value];
            return $this->{$modules}[$date];
        }
        if(!isset($this->{$modules}[$date][$field])){

            $this->{$modules}[$date][$field]  = $value;
            return $this->{$modules}[$date];
        }
        if($more){
            $this->{$modules}[$date][$field] += $value;
            return $this->{$modules}[$date];
        }
        $this->{$modules}[$date][$field] = $value;
        return $this->{$modules}[$date];
    }
}