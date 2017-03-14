<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 14.03.17
 * Time: 0:55
 */

namespace App\Http\Controllers\Issues;


use App\CommitsFileChange;
use App\issuesCommit;
use App\Project;
use App\Utilities\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CommitsFilesController extends Utility
{
    public function loadFromCommits(Request $request)
    {
        $_errors = [];
//         sleep ( 61 );
        if(!$project = Project::where('name', $request->get('project_name'))->first())
        {
            return $this->respond('Project does not exist', 404);
        }

        $commits_urls = [];
        $_record_count = 0;
        if(count($issuesCommits = issuesCommit::where('project_id', $project->id)
            ->where('files_retrieved', '0')
            ->take(5)  //in order to stick with Github's 30 requests per minute
            ->get())) {

              session_start();
              if (isset($_SESSION['timeout']) && $_SESSION['timeout'] >= time()) {
                  $diff = $_SESSION['timeout'] - time();
                  $error = ["You need to wait $diff seconds before making another request"];
                  return $this->respond($error, 503);
              }
              $_SESSION['timeout'] = time() + 75;


            foreach ($issuesCommits as $key => $issuesCommit)
            {
                $commits_url = $this->concat($issuesCommit->api_url);
                $commits_urls[] = $commits_url;
                $_commit = $this->jsonToObject($this->ping($commits_url));
                $_files = $_commit->files;

                $file_count = count($_files);
                foreach ($_files as $_file)
                {
                    $file_['project_id'] = $project->id;
                    $file_['commit_id'] = $issuesCommit->id;
                    $file_['issue_id'] = $issuesCommit->issue_id;

                    $file_['author_name'] = $_commit->commit->committer->name;

                    $file_['file'] = $_file->filename;

                    $file_['file_sha'] = $_file->sha;
                    $file_['status'] = $_file->status;

                    $file_['date_changed'] = $_commit->commit->committer->date;
//                    $commits_['description'] = ''; //to be updated when each commit is checked too

                    Model::unguard();
                    if(CommitsFileChange::firstOrCreate([
                        'project_id' => $file_['project_id'],
                        'issue_id' => $file_['issue_id'],
                        'file_sha' => $file_['file_sha'],
                    ], $file_))
                    {
                        $issuesCommit->file_changed_count = $file_count;
                        $issuesCommit->files_retrieved = $file_['file_sha'];
                        $issuesCommit->description = $_commit->commit->message;
                        $issuesCommit->update();
                        $_errors[] = true;
                        $_record_count ++;
                    }else{ $_errors[] = false;}
                    Model::reguard();
                }

            }
        }
        if(!in_array(false, $_errors))
        {
            $msg = [
                "status" => "success",
                "message" => "'{$_record_count}' record(s) successfully added to {$project->name}'s 'commits_files'",
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