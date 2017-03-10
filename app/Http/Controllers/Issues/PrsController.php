<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 9.03.17
 * Time: 23:43
 */

namespace App\Http\Controllers\Issues;


use App\Issue;
use App\IssuesPr;
use App\Project;
use App\Utilities\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PrsController extends Utility
{

    public function load(Request $request)
    {
        $_errors = [];
//         sleep ( 61 );
        if(!$project = Project::where('name', $request->get('project_name'))->first())
        {
            return $this->respond('Project does not exist', 404);
        }

        $prs = [];
        $_record_count = 0;
        if(count($issues = Issue::where('project_id', $project->id)
            ->where('pr_retrieved', '0')
            ->take(29)  //in order to stick with Github's 30 requests per minute
            ->get()))
        {

            session_start();
            if(isset($_SESSION['timeout']) && $_SESSION['timeout'] >= time()){
                $diff = $_SESSION['timeout'] - time();
                $error = ["You need to wait $diff seconds before making another request"];
                return $this->respond($error, 503);
            }
            $_SESSION['timeout'] = time() + 75;

            foreach ($issues as $key => $issue)
            {
                $_pr_url = $this->concat($issue->pr_url);
                $prs[] = $_pr_url;
                $_prs = $this->jsonToObject($this->ping($_pr_url));

                $prs_['project_id'] = $project->id;
                $prs_['issue_id'] = $issue->id;
                $prs_['pr_id'] = $_prs->id;
                $prs_['commits_counts'] = $_prs->commits;
                $prs_['merge_commit_sha'] = $_prs->merge_commit_sha; //*
                $prs_['commits_url'] = $_prs->commits_url; //*
                $prs_['merged_status'] = $_prs->merged;
                $prs_['author_id'] = $_prs->user->id;
                $prs_['author_name'] = $_prs->user->login;
                $prs_['title'] = $_prs->title;
                $prs_['description'] = $_prs->body;
                $prs_['api_url'] = $_prs->url;
                $prs_['web_url'] = $_prs->html_url;
                $prs_['state'] = $_prs->state;
                $prs_['date_created'] = $_prs->created_at;
                $prs_['date_updated'] = $_prs->updated_at;
                $prs_['date_closed'] = $_prs->closed_at;
                $prs_['date_merged'] = $_prs->merged_at; //*


                Model::unguard();
                if(IssuesPr::UpdateOrcreate([
                        'project_id' => $prs_['project_id'],
                        'pr_id' => $prs_['pr_id'],
                    ], $prs_))
                {
                    $issue->pr_retrieved = $prs_['pr_id'];
                    $issue->update();
                    $_errors[] = true;
                    $_record_count ++;
                }else{ $_errors[] = false;}
                Model::reguard();
            }
        }
         if(!in_array(false, $_errors))
         {
             $msg = [
                 "status" => "success",
                 "message" => "'{$_record_count}' record(s) successfully added to {$project->name}'s 'issues_prs'",
                 "extra" => (!$_record_count) ? 'covered' : ''
             ];
             return $this->respond($msg, 201);
         }

        return $this->respond(
            [
                'status' => 'error',
                'message' => 'something went wrong',
                "extra" =>  ''
            ]
        );
    }
}