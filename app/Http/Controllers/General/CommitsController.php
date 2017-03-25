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
use App\Utilities\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CommitsController extends Utility
{
    public function load(Request $request)
    {

        if(!$project_name = $request->get('project_name'))
        {
            return response(['error' => 'invalid project_name'], 400);
        }
//         sleep ( 61 );
        if(!$project = Project::where('name', $project_name)->first())
        {
            return $this->respond('Project does not exist', 404);
        }

        $_query = http_build_query(array_except($request->all(), ['project_name']));
        $_commits_url = substr($project->commits_url, 0, -6);
        $_commits_query_url = $this->concat($this->concat($_commits_url, $_query, '?'));

        $this->headers['headers']['If-Modified-Since'] = 'Thu, 25 Mar 2017 15:31:30 GMT+2';
        $ping = $this->ping($_commits_query_url, $this->headers, ['body', 'head'] );

        $_next =[];
        $_next['next_page'] = 'x';
        $_next['last_page'] = 'x';

        if(count($header_ = $ping->getHeader('Link')))
        {

            $links = explode(',', $header_[0]);

            if (($_fs = strpos($links[0], '<')) === 0)
                $_next['page'] =  substr($links[0], $_fs+1, strpos($links[0],'>')-1);

            if (($_ls = strpos($links[1], '<')) === 1)
                $_next['last'] =  substr($links[1], $_ls+1, strpos($links[1],'>')-2);

            if (($_fsn = strpos($links[0], "&page=")) > 0)
                $_next['next_page'] = substr($links[0], $_fsn+6, -13);

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
        $_SESSION['timeout'] = time() + 71;

        $_errors = [];
        foreach ($_commits as $_commit)
        {
            $commits_['project_id'] = $project->id;
            $commits_['commit_sha'] = $_commit->sha;
//            $commits_['author_id'] = $_commit->committer->id; //from issues
            $commits_['author_name'] = $_commit->commit->author->name;
            $commits_['api_url'] = $_commit->url; //*
            $commits_['web_url'] = $_commit->html_url;
            $commits_['description'] = $_commit->commit->message; //to be updated when each commit is checked too
            $commits_['comment_count'] = $_commit->commit->comment_count; //to be updated when each commit is checked too
            $commits_['file_changed_count'] = 0; //to be updated when each commits is checked
            $commits_['date_committed'] = $_commit->commit->author->date; //to be updated when each commits is checked

            Model::unguard();
            if(Commit::firstOrCreate([
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

        if(!in_array(false, $_errors))
        {
            $msg = [
                "status" => "success",
                'model' => 'commits',
                "message" => "'{$_record_count}' record(s) successfully loaded to {$project->name}'s 'commits'",
                "extra" => (!$_record_count || !is_numeric($_next['next_page']) ) ? 'covered' : '',
                'next' => (isset($_next['next_page'])) ? (($_next['next_page']+1 == $_next['last_page']) ? '' : $_next['next_page']) : '',
                'params' => http_build_query($requests),
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
            ],
            500
        );
    }
}