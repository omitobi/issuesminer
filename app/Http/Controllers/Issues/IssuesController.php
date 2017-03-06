<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 6.03.17
 * Time: 13:23
 */

namespace App\Http\Controllers\Issues;

use App\Issue;
use GuzzleHttp;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;

class IssuesController extends Controller
{

    public function resolve()
    {
        $done = [
            'issues' =>
                [
                    'laravel' =>
                        [
                            'link' => 'https://api.github.com/repos/laravel/laravel/issues?state=closed&since=2000-01-01%2000:00:00&per_page=100&direction=asc',
//                    'link' => 'https://api.github.com/repos/laravel/laravel/issues?state=closed',
                            'status' => 'done',
                            'file' => 'issues.json',
                            'directory' => 'laravel'
                        ],
                    'others' =>
                        [
                            'link' => 'https://api.github.com/repos/laravel/laravel/issues?state=closed',
                            'status' => 'done',
                            'file' => 'issues.json',
                            'directory' => 'others'
                        ],
                ]
        ];

        $done_obj = (object)$done;
        $headers = [
            'headers' => [
                'User-Agent' => 'omitobi',
                'Accept' => 'application/vnd.github.v3+json',
            ]
        ];


        $client = new GuzzleHttp\Client();
        $file_and_path = "app/{$done['issues']['laravel']['directory']}/{$done['issues']['laravel']['file']}";

        $response = [];
        $success = false;
        if(!file_exists(storage_path("{$file_and_path}")))
        {
//            touch("storage/{$file_and_path}");
            $res = $client->request('GET',
                $done['issues']['laravel']['link'],
                $headers);

            $result = $res->getBody();


            if(file_put_contents(storage_path("{$file_and_path}"), $result))
            {
                $success = true;
//                echo  'Successfully saved response';
            }else
            {
                $success = false;
//                echo 'Couldn\'t save response for some unknown reasons(s)';
            }
        } else { $success = true; }

        if($success)
            $response = file_get_contents(storage_path("{$file_and_path}"));

//        return response($response);
        $array_responses = json_decode($response, true);

        $final_issues = [];
        foreach ($array_responses as $idx => $array_response)
        {
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
}