<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 8.03.17
 * Time: 21:03
 */

namespace App\Http\Controllers\Issues;


use App\Project;
use App\Utilities\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProjectsController extends Utility
{
    public function store(Request $request)
    {

        $url = $request->get('url');

        if($request->get('action') != 'updatable' && Project::where('api_url', $url)->first())
        {
            return $this->respond(
                [
                    "status" => "error",
                    "message" => "project already exists",
                ]
            );
        }

        $res = $this->jsonToObject($this->ping($url, $this->headers ));

//        return $this->toArray($res);
        if($request->get('action') != 'updatable' && Project::where('identifier', $res->id)->first())
        {
            return $this->respond(
                [
                    "status" => "error",
                    "message" => "project already exists",
                ]
            );
        }
        $project['identifier'] = $res->id;
        $project['name'] = $res->name;
        $project['organization_name'] = (isset($res->organization)) ? $res->organization->login: $res->owner->login;
        $project['type'] = 'framework';
        $project['private'] = $res->private;
        $project['language'] = $res->language;
        $project['description'] = $res->description;
        $project['homepage'] = $res->homepage;
        $project['api_url'] = $res->url;
        $project['web_url'] = $res->html_url;
        $project['commits_url'] = $res->commits_url;
        $project['issues_url'] = $res->issues_url;
        $project['prs_url'] = $res->pulls_url;
        $project['date_created'] = $res->created_at;
        $project['default_branch'] = $res->default_branch;
        $project['size'] = $res->size;
        $project['merges_url'] = $res->merges_url;
        $project['labels_url'] = $res->labels_url;
        $project['languages_url'] = $res->languages_url;
        $project['contributors_url'] = $res->contributors_url;
        $project['clone_url'] = $res->clone_url;

        Model::unguard();
        if(Project::updateOrCreate(['identifier' => $project['identifier']], $project))
        {
//            $project[]['response_status'] = 'Successfully added project \''.$project['name'].'\'';

            return $this->respond(
                [
                    "status" => "success",
                    "message" => 'Successfully added project \''.$project['name'].'\'',
                    "model" => "projects",
                    'params' => $project
                ],
                201
            );
        }
        Model::reguard();

        return $this->respond(
            [
                "status" => "error",
                "message" => "Something went wrong while adding project",
                "model" => "project"
            ],
            500
        );
    }

    public function fetch(Request $request)
    {
        $requests = $request->only(['by']);

        if($requests['by'])
        {
            return $this->respond(
                [
                    'status' => 'success',
                    'message' => "retrieved projects by '{$requests['by']}'",
                    'model' => 'projects',
                    'params' => Project::all()->pluck($requests['by'])
            ]);
        }

        return $this->respond(
        [
            'status' => 'success',
            'message' => 'retrieved all projects',
            'model' => 'projects',
            'params' => Project::all()
        ]);
    }
}