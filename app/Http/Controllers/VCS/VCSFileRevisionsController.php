<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 12/04/2017
 * Time: 19:54
 */

namespace App\Http\Controllers\VCS;


use App\Commit;
use App\Project;
use App\Utilities\Utility;
use App\VCSModels\VCSExtension;
use App\VCSModels\VCSFile;
use App\VCSModels\VCSFileRevision;
use App\VCSModels\VCSFiletype;
use App\VCSModels\VCSProject;
use App\VCSModels\VCSTextFileRevision;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class VCSFileRevisionsController extends Utility
{

    public function sortRevisions(Request $request)
    {
        return $this->loadFromCommits($request);
    }

    public function loadFromCommits(Request $request)
    {

        $_errors = [];
//         sleep ( 61 );
        if(!$project = VCSProject::where('name', $request->get('project_name'))->first())
        {
            return $this->respond('Project does not exist', 404);
        }

        $commits_urls = [];
        $_record_count = 0;
        $request_count = 0;
        $text_files_revision_counts = 0;
        if(count($commits = Commit::where('project_id', $project->Id)
            ->where('touched', '0')->orderBy('date_committed', 'asc')
            ->take(80)  //in order to stick with Github's 83.33 requests per minute
            ->get())) {

            $prev_rev = VCSFileRevision::where('ProjectId', $project->Id)->orderBy('Id', 'desc')->first();
            $prev_rev_id =  $prev_rev ? $prev_rev->Id : 1;

            //            session_start();
//            if (isset($_SESSION['timeout']) && $_SESSION['timeout'] >= time()) {
//                $diff = $_SESSION['timeout'] - time();
//                $error = ["You need to wait $diff seconds before making another request"];
//                return $this->respond($error, 503);
//            }
//            $_SESSION['timeout'] = time() + 70;rer

            $this->headers['headers']['Accept']  = 'application/vnd.github.v3.full+json';
            foreach ($commits as $key => $commit)
            {
                $commits_url = $this->concat($commit->api_url);
                $commits_urls[] = $commits_url;
                $_commit = $this->jsonToObject($this->ping($commits_url, $this->headers, ['body'], 'GET', true));
                $_files = $_commit->files;

                $fileAdded = 0;
                $fileDeleted = 0;
                $fileModified = 0;
                $isTouched = [];

                $file_count = count($_files);
                if ($file_count) {

//                    return $this->respond($_commit);
                    foreach ($_files as $_file) {
//                    return $this->toArray($_commit);
                        $ext = pathinfo($_file->filename, PATHINFO_EXTENSION);

                        $_type = (isset($this->types_[mb_strtolower($ext)])) ? $this->types_[mb_strtolower($ext)] : mb_strtoupper($ext);


                        $file_['ProjectId'] = $project->Id;
//                    $file_['Name'] = $commit->id;
                        $file_['Name'] = $_file->sha;
//                    $file_['issue_id'] = $commit->issue_id;


//                    $file_['FileId'] = $_file->filename;
                        $the_file = $project->VCSFiles()->firstOrCreate(['Name' => $_file->filename]);

                        $file_['FileId'] = ($the_file) ? $the_file->Id : 0;
                        $file_['CommitId'] = $commit->id;

                        $file_['Date'] = str_replace(['T', 'Z'], [' ', ''], $commit->date_committed);
                        $file_['Comment'] = $_commit->commit->message;
                        $file_['PreviousRevisionId'] = $prev_rev_id;
                        $file_['Alias'] = $_file->filename;
                        $file_['ProjectLOC'] = 0;  //to be added to project table
//                        $file_['CommitterId'] = isset($_commit->committer) ? $_commit->committer->login : (
//                            ($_commit->commit->committer-> name).'|'.($_commit->commit->committer->email));

                        $file_['CommitterId'] = (isset($_commit->author)) ? $_commit->author->id : 0;
                        $file_['Extension'] = '.' . $ext;

                        $file_['FiletypeId'] = VCSFiletype::firstOrCreate(
                            ['Type' => $_type],
                            [
                                'Type' => $_type,
                                'IsText' => in_array(mb_strtolower($ext), $this->texts),
                                'IsXML' => in_array(mb_strtolower($ext), $this->xmls),
                                'IsImperative' => in_array(mb_strtolower($ext), $this->imp_langs),
                                'IsOO' => in_array(mb_strtolower($ext), $this->oo_langs)
                            ])->Id;
                        $file_['ExtensionId'] = VCSExtension::firstOrCreate(
                            ['Extension' => '.' . $ext],
                            [
                                'Extension' => '.' . $ext,
                                'Type' => $_type,
                                'IsText' => in_array(mb_strtolower($ext), $this->texts),
                                'IsXML' => in_array(mb_strtolower($ext), $this->xmls),
                                'TypeId' =>$file_['FiletypeId'],
                            ])->Id;
                        $file_['AuthorEmail'] = $_commit->commit->author->email;
                        $file_['AuthorName'] = $_commit->commit->author->name;
                        $file_['AddedCodeLines'] = $_file->additions;
                        $file_['RemovedCodeLines'] = $_file->deletions;
                        $file_['status'] = $_file->status;

                        $fileAdded += $_file->status === 'added' ? 1 : 0;
                        $fileModified += $_file->status === 'modified' ? 1 : 0;
                        $fileDeleted += $_file->status === 'removed' ? 1 : 0;

                        $file_['changes'] = $_file->changes;
                        $file_['patch'] = (!isset($_file->patch)) ?:$_file->patch;
                        $file_['ContentsU'] = '0';
//                        $content = (!isset($_file->contents_url)) ?0: $this->ping($_file->contents_url, $this->headers, ['body'], 'GET', true);
                        try{
                            $file_['ContentsU'] = (!isset($_file->raw_url) || !$_file->raw_url) ?'0': (string)$this->ping($_file->raw_url, [], ['body'], 'GET', true);
////                            if(!$_file->raw_url){
//                                 $headers = $this->headers;
//                                $headers['headers']['Accept']  = 'application/vnd.github.v3.raw';
//                                $file_['ContentsU'] = !$_file->contents_url ?:(string)$this->ping($_file->contents_url, $headers, ['body'], 'GET', true);
////                            }
                        } catch ( ClientException $exception){
                            $file_['ContentsU'] = '0';
                        }
                        $substr_count = $file_['ContentsU'] == '0' ? 0 : substr_count($file_['ContentsU'], "\n")+1;
                        $file_['LinesOfCode'] = $substr_count;

//                    $commits_['description'] = ''; //to be updated when each commit is checked too

                        Model::unguard();
                        if ($vcsfilerevision = VCSFileRevision::updateOrCreate(
                            array_only($file_, ['ProjectId', 'CommitId', 'Alias', 'Date']),
                            array_except($file_, ['ProjectId', 'CommitId', 'Alias', 'Date']))
                        ) {
                            $prev_rev_id = $vcsfilerevision->Id;
                            $isTouched[] = $vcsfilerevision->Id;

                            if(in_array(mb_strtolower($ext), $this->texts)){
                                $text_files_revision_counts++;

                                //todo: add lines of code to the VCSTextFileRevision table
//                                $vcstextfilerevision['RevisionId'] = $vcsfilerevision->Id;
//                            $vcstextfilerevision['CodeChurnLines'] = NULL;
                                $vcstextfilerevision['AddedCodeLines'] = $_file->additions;
                                $vcstextfilerevision['RemovedCodeLines'] = $_file->deletions;
                                $vcstextfilerevision['LinesOfCode'] = $file_['LinesOfCode'];

                                $vcstextfilerevision['ContentsU'] = $file_['ContentsU'];
                                $vcstextfilerevision['CompressedContents'] = '0';

                                $vcstextfilerevision['status'] = $_file->status;

                                $vcstextfilerevision['CommitId'] = $commit->id;
                                $vcstextfilerevision['FileId'] = $vcsfilerevision->FileId;
                                $vcstextfilerevision['ProjectId'] = $vcsfilerevision->ProjectId;

                                $vcstextfilerevision['changes'] = $_file->changes;
                                $vcstextfilerevision['Alias'] = $_file->filename;
                                $vcstextfilerevision['patch'] = (!isset($_file->patch)) ?:$_file->patch;

                                VCSTextFileRevision::updateOrCreate(['RevisionId' =>  $vcsfilerevision->Id], $vcstextfilerevision);
                            }

                            $_errors[] = false;
                            $_record_count++;
                        } else {
                            $_errors[] = true;
                        }
                        Model::reguard();
                    }
                    if($is_touched_count = count($isTouched)){
                        $commit->author_name = $_commit->commit->author->name;
                        $commit->author_email =  $_commit->commit->author->email;
                        $commit->author_username = (isset($_commit->author)) ? $_commit->author->login : 0;
                        $commit->file_changed_count = $file_count;
                        if($_commit->stats){
                            $commit->additions = $_commit->stats->additions;
                            $commit->deletions = $_commit->stats->deletions;
                            $commit->total = $_commit->stats->total;
                        }
                        $commit->file_added = $fileAdded;
                        $commit->file_removed = $fileDeleted;
                        $commit->file_modified = $fileModified;
                        $commit->touched = json_encode($isTouched);
                        $commit->description = $_commit->commit->message;
                        $commit->update();
                    }

                }
                $request_count ++;
            }
            if($request_count >= 28) {
                $msg = [
                    "status" => "success",
                    "message" => "'{$_record_count}' record(s) successfully added to {$project->Name}'s 'VCSFileRevisions'. "
                        ."{$text_files_revision_counts} added/updated for VCSTextfileRevision",
                    "extra" => (!$_record_count) ? 'covered' : '',
                    "params" => '',
                    "request_count" => $request_count
                ];
                return $this->respond($msg, 201);
            }
        }
        if(!in_array(true, $_errors)) {
            $msg = [
                "status" => "success",
                "message" => "'{$_record_count}' record(s) successfully added to {$project->Name}'s 'VCSFileRevisions'. "
                        ."{$text_files_revision_counts} added/updated for VCSTextfileRevision",
                "extra" => (!$_record_count) ? 'covered' : '',
                "params" => ''
            ];
            return $this->respond($msg, 201);
        }

        return $this->respond(
            [
                'status' => 'error',
                'message' => 'something went wrong',
                "extra" =>  ''
            ],
            500
        );
    }


    public function updateAll(Request $request)
    {
        $_errors = [];
//         sleep ( 61 );
        if(!$project = VCSProject::where('name', $request->get('project_name'))->first())
        {
            return $this->respond('Project does not exist', 404);
        }

        $commits_urls = [];
        $_record_count = 0;
        $request_count = 0;
        $text_files_revision_counts = 0;
        if(count($commits = Commit::where('project_id', $project->Id)
            ->where('touched', '0')->orderBy('date_committed', 'asc')
            ->take(80)  //in order to stick with Github's 83.33 requests per minute
            ->get())) {

            $prev_rev = VCSFileRevision::where('ProjectId', $project->Id)->orderBy('Id', 'desc')->first();
            $prev_rev_id =  $prev_rev ? $prev_rev->Id : 1;

            //            session_start();
//            if (isset($_SESSION['timeout']) && $_SESSION['timeout'] >= time()) {
//                $diff = $_SESSION['timeout'] - time();
//                $error = ["You need to wait $diff seconds before making another request"];
//                return $this->respond($error, 503);
//            }
//            $_SESSION['timeout'] = time() + 70;rer


            foreach ($commits as $key => $commit)
            {
                $commits_url = $this->concat($commit->api_url);
                $commits_urls[] = $commits_url;
                $_commit = $this->jsonToObject($this->ping($commits_url, [], ['body'], 'GET', true));
                $_files = $_commit->files;

//                return $this->respond($_commit);
                $file_count = count($_files);
                if ($file_count) {

//                    return $this->respond($_commit);
                    foreach ($_files as $_file) {
//                    return $this->toArray($_commit);
                        $ext = pathinfo($_file->filename, PATHINFO_EXTENSION);

                        $_type = (isset($this->types_[mb_strtolower($ext)])) ? $this->types_[mb_strtolower($ext)] : mb_strtoupper($ext);


                        $file_['ProjectId'] = $project->Id;
//                    $file_['Name'] = $commit->id;
                        $file_['Name'] = $_file->sha;
//                    $file_['issue_id'] = $commit->issue_id;


//                    $file_['FileId'] = $_file->filename;
                        $the_file = VCSFile::where('Name', $_file->filename)->first();
                        $file_['FileId'] = ($the_file) ? $the_file->Id : 0;
                        $file_['CommitId'] = $commit->id;

                        $file_['Date'] = str_replace(['T', 'Z'], [' ', ''], $_commit->commit->author->date);
                        $file_['Comment'] = $_commit->commit->message;
                        $file_['PreviousRevisionId'] = $prev_rev_id;
                        $file_['Alias'] = $_file->filename;
                        $file_['ProjectLOC'] = 0;  //to be added to project table
//                        $file_['CommitterId'] = isset($_commit->committer) ? $_commit->committer->login : (
//                            ($_commit->commit->committer-> name).'|'.($_commit->commit->committer->email));

                        $file_['CommitterId'] = isset($_commit->author) ? $_commit->author->id :
                            (($_commit->committer) ? $_commit->committer->id : $_commit->commit->author->email);
                        $file_['Extension'] = '.' . $ext;

                        $file_['FiletypeId'] = VCSFiletype::firstOrCreate(
                            ['Type' => $_type],
                            [
                                'Type' => $_type,
                                'IsText' => in_array(mb_strtolower($ext), $this->texts),
                                'IsXML' => in_array(mb_strtolower($ext), $this->xmls),
                                'IsImperative' => in_array(mb_strtolower($ext), $this->imp_langs),
                                'IsOO' => in_array(mb_strtolower($ext), $this->oo_langs)
                            ])->Id;
                        $file_['ExtensionId'] = VCSExtension::firstOrCreate(
                            ['Extension' => '.' . $ext],
                            [
                                'Extension' => '.' . $ext,
                                'Type' => $_type,
                                'IsText' => in_array(mb_strtolower($ext), $this->texts),
                                'IsXML' => in_array(mb_strtolower($ext), $this->xmls),
                                'TypeId' => 0,
                            ])->Id;
//                        return $file_;

//                    $commits_['description'] = ''; //to be updated when each commit is checked too

                        Model::unguard();
                        if ($vcsfilerevision = VCSFileRevision::updateOrCreate(
                            array_except($file_, ['CommitterId', 'Date']),
                            array_only($file_, ['CommitterId', 'Date']) )
                        ) {
                            $prev_rev_id = $vcsfilerevision->Id;
                            $commit->author_id = $file_['CommitterId'];
                            $commit->file_changed_count = $file_count;
                            $commit->touched = $file_['Name'];
                            $commit->description = $_commit->commit->message;
                            $commit->update();

                            if(in_array(mb_strtolower($ext), $this->texts)){
                                $text_files_revision_counts++;

                                //todo: add lines of code to the VCSTextFileRevision table
//                                $vcstextfilerevision['RevisionId'] = $vcsfilerevision->Id;
//                            $vcstextfilerevision['CodeChurnLines'] = NULL;
                                $vcstextfilerevision['AddedCodeLines'] = $_file->additions;
                                $vcstextfilerevision['RemovedCodeLines'] = $_file->deletions;
                                $vcstextfilerevision['LinesOfCode'] = 0;

                                $vcstextfilerevision['ContentsU'] = '0';
                                $vcstextfilerevision['CompressedContents'] = '0';

                                $vcstextfilerevision['status'] = $_file->status;

                                $vcstextfilerevision['CommitId'] = $commit->id;
                                $vcstextfilerevision['FileId'] = $vcsfilerevision->FileId;
                                $vcstextfilerevision['ProjectId'] = $vcsfilerevision->ProjectId;

                                $vcstextfilerevision['changes'] = $_file->changes;
                                $vcstextfilerevision['Alias'] = $_file->filename;
                                $vcstextfilerevision['patch'] = (!isset($_file->patch)) ?:$_file->patch;
                                try{
                                    $content = (!isset($_file->raw_url)) ?: $this->ping($_file->raw_url, [], ['body'], 'GET', true);
                                    $vcstextfilerevision['ContentsU'] = $content;
                                } catch ( ClientException $exception){
                                    $vcstextfilerevision['ContentsU'] = 0;
                                }
                                VCSTextFileRevision::updateOrCreate(['RevisionId' =>  $vcsfilerevision->Id], $vcstextfilerevision);
                            }

                            $_errors[] = false;
                            $_record_count++;
                        } else {
                            $_errors[] = true;
                        }
                        Model::reguard();
                    }

                }
                $request_count ++;
            }
            if($request_count >= 28) {
                $msg = [
                    "status" => "success",
                    "message" => "'{$_record_count}' record(s) successfully added to {$project->Name}'s 'VCSFileRevisions'. "
                        ."{$text_files_revision_counts} added/updated for VCSTextfileRevision",
                    "extra" => (!$_record_count) ? 'covered' : '',
                    "params" => '',
                    "request_count" => $request_count
                ];
                return $this->respond($msg, 201);
            }
        }
        if(!in_array(true, $_errors)) {
            $msg = [
                "status" => "success",
                "message" => "'{$_record_count}' record(s) successfully added to {$project->Name}'s 'VCSFileRevisions'. "
                    ."{$text_files_revision_counts} added/updated for VCSTextfileRevision",
                "extra" => (!$_record_count) ? 'covered' : '',
                "params" => ''
            ];
            return $this->respond($msg, 201);
        }

        return $this->respond(
            [
                'status' => 'error',
                'message' => 'something went wrong',
                "extra" =>  ''
            ],
            500
        );
    }
}