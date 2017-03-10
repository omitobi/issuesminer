<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 6.03.17
 * Time: 13:23
 */

namespace App\Http\Controllers\Issues;

use App\Issue;
use App\Project;
use App\Utilities\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;

class IssuesController extends Utility
{

    public function load(Request $request)
    {

        if(!$project_name = $request->get('project_name'))
        {
            return response(['error' => 'invalid project_name'], 400);
        }

        if(!$request->get('is_update'))
        {

            $project = Project::where('name', $project_name)->first();

            $_query = http_build_query(array_except($request->all(), ['project_name']));
            $issue_url = substr($project->issues_url, 0, -9);
            $issues_query_url = $this->concat($issue_url, $_query, '?');

            $in_issues = $this->jsonToArray($this->ping($issues_query_url, $this->headers));


            $final_issues = [];
            foreach ($in_issues as $idx => $in_issue) {
                $final_issues['issues'][$idx]['project_id'] = $project->id;
                $final_issues['issues'][$idx]['identifier'] = $in_issue['id'];
                $final_issues['issues'][$idx]['number'] = $in_issue['number'];
                $final_issues['issues'][$idx]['title'] = $in_issue['title'];
                $final_issues['issues'][$idx]['reporter_name'] = $in_issue['user']['login'];
                $final_issues['issues'][$idx]['state'] = $in_issue['state'];
                $final_issues['issues'][$idx]['description'] = $in_issue['body'];
                $final_issues['issues'][$idx]['api_url'] = $in_issue['url'];
                $final_issues['issues'][$idx]['web_url'] = $in_issue['html_url'];
                $final_issues['issues'][$idx]['pr_url'] = $in_issue['pull_request']['url'];
                $final_issues['issues'][$idx]['date_created'] = $in_issue['created_at'];
                $final_issues['issues'][$idx]['date_updated'] = $in_issue['updated_at'];
                $final_issues['issues'][$idx]['date_closed'] = $in_issue['closed_at'];
            }
//        if(!Issue::all()->count()) {
            Model::unguard();
            foreach ($final_issues['issues'] as $final_issue) {
                Issue::UpdateOrcreate([
                    'project_id' => $final_issue['project_id'],
                    'identifier' => $final_issue['identifier'],
                ], $final_issue);
            }
            Model::reguard();

            $final_response[]['response_status'] = 'Successfully loaded issues!';
            return response()->json($final_response, 201);
//            return response($issues);


        }

        response()->json(['something went wrong']);
    }














































    public function resolve(Request $request)
    {

        /*$done = $this->setDone()->getDone();*/

      /*  $done_obj = (object)$done;
        */
/*
 *
        if($first_call = $this->firstCall($request, $done))
        {
            return $first_call;
        }*/

//        return $this->firstCall($request, $done);
//        return response($this->pullCall());
        return response()->json(['something went wrong'], 500);
    }



    public function pullCall()
    {
        $first_issue = Issue::first();

        if(!file_exists(storage_path('app/laravel/pr1.json')))
        {
            $pr_ = $this->ping($first_issue->pr_url);
        } else
        {
            $pr_ = $this->getContents('app/laravel/pr1.json');
        }

        if($this->addContent(storage_path('app/laravel/pr1.json'), $pr_))
        {
            $pr_array_ = json_decode($pr_, true);
            $pr_commits_url_ = $pr_array_['commits_url'];
            $pr_commits_ = json_decode($this->ping($pr_commits_url_), true);
            $pr_commit_url = "https://api.github.com/repos/laravel/laravel/commits/{$pr_commits_[0]['sha']}";
            $commit_ = json_decode($this->ping($pr_commit_url), true);
            $files_affected = [];
            foreach ($commit_['files'] as $file_affected)
            {
                $files_affected[] = $file_affected['filename'];
            }
            return $files_affected;

        }

        return [];
    }

    protected function addContent($file, $content, $update = null)
    {
        $success = false;
        if(file_exists($file) && !$update)
        {
            return true;
        }

        try{
            if(file_put_contents($file, $content, $update))
            {
                $success = true;
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $success;
    }



    protected function firstCall($request, $done)
    {
        $to_update = $request->get('to_update') ? FILE_APPEND  : null ;

        $headers = [
            'headers' => [
                'User-Agent' => 'omitobi',
                'Accept' => 'application/vnd.github.v3+json',
            ]
        ];


        $file_and_path = $this->___path('issues', $done[0]['repo']);


        $response = [];
        $success = false;
        $result = '';
        if($to_update || !file_exists("{$file_and_path}")) {
//            touch("storage/{$file_and_path}")
            $result = $this->ping($done['issues']['laravel']['link'], $headers);
        }

        if($result && $this->addContent($file_and_path, $result, $to_update))
        {
            $success = true;
        }

        if($response = file_get_contents("{$file_and_path}"))
        {
            $success = true;
        }

        if ($success && $response) {
//        return response($response);
            $array_responses = json_decode($response, true);

            $final_issues = [];
            foreach ($array_responses as $idx => $array_response) {
                $final_issues['issues'][$idx]['url'] = $array_response['url'];
                $final_issues['issues'][$idx]['identifier'] = $array_response['id'];
                $final_issues['issues'][$idx]['number'] = $array_response['number'];
                $final_issues['issues'][$idx]['title'] = $array_response['title'];
                $final_issues['issues'][$idx]['reporter_name'] = $array_response['user']['login'];
                $final_issues['issues'][$idx]['state'] = $array_response['state'];
                $final_issues['issues'][$idx]['description'] = $array_response['body'];
                $final_issues['issues'][$idx]['pr_url'] = $array_response['pull_request']['url'];
                $final_issues['issues'][$idx]['date_created'] = $array_response['created_at'];
                $final_issues['issues'][$idx]['date_updated'] = $array_response['updated_at'];
                $final_issues['issues'][$idx]['date_closed'] = $array_response['closed_at'];
            }
//        if(!Issue::all()->count()) {
            Model::unguard();
            foreach ($final_issues['issues'] as $final_issue) {
                Issue::UpdateOrcreate(['identifier' => $final_issue['identifier']], $final_issue);
            }
            Model::reguard();
//        }

            return Issue::first();
            return response()->json($final_issues);
        }
        return false;
    }
    
}