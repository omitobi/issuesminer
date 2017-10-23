<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 25/03/2017
 * Time: 09:01
 */

namespace App\Http\Controllers\General;

use App\Commit;
use App\Project;
use App\Utilities\Util;
use App\Utilities\Utility;
use App\VCSModels\VCSFileRevision;
use App\VCSModels\VCSProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7;


class CommitsController extends Utility
{

    use Util;

    public function untouch_commits(Request $request)
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

        $vcs_project->commits()->update(['touched' => '0']);

        return $vcs_project;
    }

    public function load(Request $request)
    {

        if(!$project_name = $request->get('project_name')) {
            return response(['error' => 'invalid project_name'], 400);
        }
//         sleep ( 61 );
        if(!$project = Project::where('name', $project_name)->first()) {
            return $this->respond('Project does not exist', 404);
        }

        $_query = http_build_query(array_except($request->all(), ['project_name']));
        $_commits_url = substr($project->commits_url, 0, -6);

        $_commits_query_url = $this->concat($this->concat($_commits_url), $_query, '&');

//        $this->headers['headers']['If-Modified-Since'] = 'Thu, 25 Mar 2017 15:31:30 GMT+2';
        $ping = $this->ping($_commits_query_url, $this->headers, ['body', 'head'] );

        $_next =[];
        $_next['next_page'] = 'x';
        $_next['last_page'] = 'x';

        if(count($header_ = $ping->getHeader('Link'))) {

            $links = explode(',', $header_[0]);

//            return $parsed = Psr7\parse_header($ping->getHeader('Link'));
            if (($_fs = strpos($links[0], '<')) === 0)
                $_next['page'] =  substr($links[0], $_fs+1, strpos($links[0],'>')-1);

            if (($_ls = strpos($links[1], '<')) === 1)
                $_next['last'] =  substr($links[1], $_ls+1, strpos($links[1],'>')-2);

            if (($_fsn = strpos($links[0], "&page=")) > 0)
                $_next['next_page'] = substr($links[0], $_fsn + 6, -13);

            if (($_lsn = strpos($links[1], "&page=")) > 0)
                $_next['last_page'] = substr($links[1], $_lsn+6, -13);

        }

        $_commits = $this->jsonToObject($ping->getBody());


        $commits_urls = [];
        $_record_count = 0;

        session_start();
        if (isset($_SESSION['timeout']) && $_SESSION['timeout'] >= time()) {
            $diff = $_SESSION['timeout'] - time();
            $error = ["You need to wait $diff seconds before making another request"];
            return $this->respond($error, 503);
        }
        $_SESSION['timeout'] = time() + 5;

        $_errors = [];
        foreach ($_commits as $_commit)
        {
            $commits_['project_id'] = $project->id;
            $commits_['commit_sha'] = $_commit->sha;
//            $commits_['author_id'] =
//                ($_commit->committer->id) ? $_commit->committer->id : $_commit->author->id; //from issuesCommit
            $commits_['api_url'] = $_commit->url; //*
            $commits_['web_url'] = $_commit->html_url;
            $commits_['description'] = $_commit->commit->message; //to be updated when each commit is checked too
            $commits_['comment_count'] = $_commit->commit->comment_count; //to be updated when each commit is checked too
            $commits_['file_changed_count'] = 0; //to be updated when each commits is checked
            $commits_['date_committed'] = $_commit->commit->author->date; //to be updated when each commits is checked


            $commits_['author_id'] =
                isset($_commit->author) ? $_commit->author->id : 0; //from issuesCommit
            $commits_['author_name'] = $_commit->commit->author->name;
            $commits_['author_username'] = isset($_commit->author) ? $_commit->author->login : 0;
            $commits_['author_email'] = $_commit->commit->author->email;

            Model::unguard();
            if(Commit::updateOrCreate([
                'project_id' => $commits_['project_id'],
                'commit_sha' => $commits_['commit_sha']
            ], $commits_))
            {
                $_errors[] = true;
                $_record_count ++;
            }else{ $_errors[] = false;}
            Model::reguard();
        }


        $requests = $request->all();
        $requests['page'] = (isset($_next['next_page'])) ? $_next['next_page'] : '';

        if(!in_array(false, $_errors)) {
            $msg = [
                "status" => "success",
                'model' => 'commits',
                "message" => "'{$_record_count}' record(s) successfully loaded to {$project->name}'s 'commits'",
                "extra" => (!$_record_count || !is_numeric($_next['next_page']) ) ? 'covered' : '',
                'next' => (isset($_next['next_page'])) ? (($_next['next_page']+1 == $_next['last_page']) ? '' : $_next['next_page']) : '',
                'params' => http_build_query($requests),
                'notes' => [
                    array_except($_next, ['last','page'])
                ]
            ];
            return $this->respond($msg, 201);
        }

        return $this->respond(
            $msg = [
                "status" => "error",
                'model' => 'commits',
                "message" => "Something went wrong",
                "extra" => '',
                'next' => (isset($_next['next_page'])) ? (($_next['next_page']+1 == $_next['last_page']) ? '' : $_next['next_page']) : '',
                'notes' => [
                    array_except($_next, ['last','page'])
                ],
            ],
            500
        );
    }


















public function updateCommitsWithoutEmail(Request $request)
{
    try{
        $project_name = $request->get('project_name');
        $project = $this->getProject($project_name);

    } catch (ModelNotFoundException $exception) {

        return $this->respond('Project does not exist', 404);
    }

    $commits = $project->commits()->whereNull('author_email')->take(35)->get();

    $commits->each( function ($commit) {
            $result =  $this->jsonToObject($this->ping($this->concat($commit->api_url), $this->headers)->getContents());
            $commit->author_email = $result->commit->author->email;
            $commit->author_name = $result->commit->author->name;
            $commit->touched = '0';
            $commit->update();

    });

    $count = $commits->count();

    $msg = [
        "status" => "success",
        "message" => "'{$count}' record(s) successfully UPDATED to {$project->Name}'s 'commits'. ",
        "extra" => (! $count) ? 'covered' : '',
        "params" => ''
    ];

    return $this->respond($msg, 201);
}















    public function updateAll(Request $request)
    {
        if(!$project_name = $request->get('project_name')) {
            return response(['error' => 'invalid project_name'], 400);
        }
//         sleep ( 61 );
        if(!$project = Project::where('name', $project_name)->first()) {
            return $this->respond('Project does not exist', 404);
        }

        $_query = http_build_query(array_except($request->all(), ['project_name']));
        $_commits_url = substr($project->commits_url, 0, -6);

        $_commits_query_url = $this->concat($this->concat($_commits_url), $_query, '&');

//        $this->headers['headers']['If-Modified-Since'] = 'Thu, 25 Mar 2017 15:31:30 GMT+2';
        $ping = $this->ping($_commits_query_url, $this->headers, ['body', 'head'] );

        $_next =[];
        $_next['next_page'] = 'x';
        $_next['last_page'] = 'x';

        if(count($header_ = $ping->getHeader('Link'))) {

            $links = explode(',', $header_[0]);

//            return $parsed = Psr7\parse_header($ping->getHeader('Link'));
            if (($_fs = strpos($links[0], '<')) === 0)
                $_next['page'] =  substr($links[0], $_fs+1, strpos($links[0],'>')-1);

            if (($_ls = strpos($links[1], '<')) === 1)
                $_next['last'] =  substr($links[1], $_ls+1, strpos($links[1],'>')-2);

            if (($_fsn = strpos($links[0], "&page=")) > 0)
                $_next['next_page'] = substr($links[0], $_fsn + 6, -13);

            if (($_lsn = strpos($links[1], "&page=")) > 0)
                $_next['last_page'] = substr($links[1], $_lsn+6, -13);

        }

        $_commits = $this->jsonToObject($ping->getBody());


        $commits_urls = [];
        $_record_count = 0;

        session_start();
        if (isset($_SESSION['timeout']) && $_SESSION['timeout'] >= time()) {
            $diff = $_SESSION['timeout'] - time();
            $error = ["You need to wait $diff seconds before making another request"];
            return $this->respond($error, 503);
        }
        $_SESSION['timeout'] = time() + 5;

        $commits_updated = [];
        $_errors = [];
        foreach ($_commits as $_commit)
        {
            $commits_['project_id'] = $project->id;
            $commits_['commit_sha'] = $_commit->sha;
            $commits_['author_username'] = isset($_commit->author) ? $_commit->author->login : 0;
            $commits_['author_id'] =
                isset($_commit->author) ? $_commit->author->id : 0; //from issuesCommit
            $commits_['author_name'] = $_commit->commit->author->name;
            $commits_['author_email'] = $_commit->commit->author->email;
//            $commits_['file_added'] = $_commit->commit->author->name;
//            $commits_['file_removed'] = $_commit->commit->author->name;
//            $commits_['file_removed'] = $_commit->commit->author->name;
//            $commits_['file_modified'] = $_commit->url; //*
//            $commits_['additions'] = $_commit->url; //*
//            $commits_['deletions'] = $_commit->url; //*
//            $commits_['total'] = $_commit->url; //*
//            $commits_['date_committed'] = $_commit->commit->author->date;
            $to_update = array_except($commits_, ['project_id', 'commit_sha']);
            if($comm = Commit::findOrUpdate([
                'project_id' => $commits_['project_id'],
                'commit_sha' => $commits_['commit_sha']
            ], $to_update))
            {
                $commits_updated[] = [
                    'commit_id' => $comm->id,
                    'commit_sha' => $comm->commit_sha,
                    'commit_url' => $comm->api_url
                ];

                $to_update_in_revision = [
                    'CommitterId' => $commits_['author_id'],
                    'AuthorEmail' => $commits_['author_email'],
                    'AuthorName'  => $commits_['author_name'],
                    'Date'        => $comm->commit->author->date
                ];
                $comm->vcsFileRevisions()
                    ->where('ProjectId', $commits_['project_id'])
                    ->update($to_update_in_revision);

                $_errors[] = true;
                $_record_count ++;
            }else{ $_errors[] = false;}
            Model::reguard();
        }


        $requests = $request->all();
        $requests['page'] = (isset($_next['next_page'])) ? $_next['next_page'] : '';

        if(!in_array(false, $_errors)) {
            $msg = [
                "status" => "success",
                'model' => 'commits',
                "message" => "'{$_record_count}' record(s) successfully updated to {$project->name}'s 'commits'",
                "extra" => (!$_record_count || !is_numeric($_next['next_page']) ) ? 'covered' : '',
                'next' => (isset($_next['next_page'])) ? (($_next['next_page']+1 == $_next['last_page']) ? '' : $_next['next_page']) : '',
                'params' => http_build_query($requests),
                'notes' => [
                    array_except($_next, ['last','page'])
                ],
                'others' => $commits_updated,
            ];
            return $this->respond($msg, 201);
        }

        return $this->respond(
            $msg = [
                "status" => "error",
                'model' => 'commits',
                "message" => "Something went wrong",
                "extra" => '',
                'next' => (isset($_next['next_page'])) ? (($_next['next_page']+1 == $_next['last_page']) ? '' : $_next['next_page']) : '',
                'notes' => [
                    array_except($_next, ['last','page'])
                ],
                'others' => $commits_updated,
            ],
            500
        );
    }
}