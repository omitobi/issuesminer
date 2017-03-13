<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 13.03.17
 * Time: 23:50
 */

namespace App\Http\Controllers\Issues;

use App\issuesCommit;
use App\IssuesPr;
use App\Project;
use App\Utilities\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CommitsController extends Utility
{

    public function loadFromPrs(Request $request)
    {
        $_errors = [];
//         sleep ( 61 );
        if(!$project = Project::where('name', $request->get('project_name'))->first())
        {
            return $this->respond('Project does not exist', 404);
        }

        $commits_urls = [];
        $_record_count = 0;
        if(count($issuesPrs = IssuesPr::where('project_id', $project->id)
            ->where('commits_retrieved', '0')
            ->take(5)  //in order to stick with Github's 30 requests per minute
            ->get())) {

          /*  session_start();
            if (isset($_SESSION['timeout']) && $_SESSION['timeout'] >= time()) {
                $diff = $_SESSION['timeout'] - time();
                $error = ["You need to wait $diff seconds before making another request"];
                return $this->respond($error, 503);
            }
            $_SESSION['timeout'] = time() + 75;*/


            foreach ($issuesPrs as $key => $issuePr)
            {
                $pr_commits_url = $this->concat($issuePr->api_url.'/commits');
                $commits_urls[] = $pr_commits_url;
                $_commits = $this->jsonToObject($this->ping($pr_commits_url));

                foreach ($_commits as $_commit)
                {
                    $commits_['project_id'] = $project->id;
                    $commits_['issue_id'] = $issuePr->issue_id;
                    $commits_['author_id'] = $issuePr->author_id; //from issues
                    $commits_['commit_sha'] = $_commit->sha;
                    $commits_['author_name'] = $_commit->commit->committer->name;
                    $commits_['api_url'] = $_commit->url; //*
                    $commits_['web_url'] = $_commit->html_url;
                    $commits_['file_changed_count'] = 0; //to be updated when each commits is checked
                    $commits_['date_committed'] = ''; //to be updated when each commits is checked
//                    $commits_['description'] = ''; //to be updated when each commit is checked too

                    Model::unguard();
                    if(issuesCommit::firstOrCreate([
                        'project_id' => $commits_['project_id'],
                        'issue_id' => $commits_['commit_sha']
                    ], $commits_))
                    {
                        $issuePr->commits_retrieved = $commits_['commit_sha'];
                        $issuePr->update();
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
                "message" => "'{$_record_count}' record(s) successfully added to {$project->name}'s 'prs_commits'",
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