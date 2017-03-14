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
use Illuminate\Http\Request;

class ProjectsController extends Utility
{
    public function store(Request $request)
    {

        $url = $request->get('url');

        if(Project::where('api_url', $url)->first())
        {
            return response()->json(null, 204);
        }

        $res = $this->jsonToObject($this->ping($url, $this->headers ));

//        return $this->toArray($res);
        if(Project::where('identifier', $res->id)->first())
        {
            return response()->json(null, 204);
        }
        $project['identifier'] = $res->id;
        $project['organization_name'] = (isset($res->organization)) ? $res->organization->login: 'Unknown';
        $project['name'] = $res->name;
        $project['type'] = 'framework';
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
        
        if(Project::UpdateOrCreate(['identifier' => $project['identifier']], $project))
        {
            $project[]['response_status'] = 'Successfully added project \''.$project['name'].'\'';
            return response($project, 201);
        }

        return response()->json(['error' => 'something went wrong']);
    }

    public function fetch()
    {
        return Project::all();
    }
}