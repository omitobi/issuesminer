<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 8.03.17
 * Time: 13:49
 */

namespace App\Utilities;

use GuzzleHttp;

use App\Http\Controllers\Controller;

class Utility extends Controller
{
    const BEFORE_STRING = 0;
    const AFTER_STRING = 1;

    protected $headers = [
                'headers' => [
                    'User-Agent' => 'omitobi',
                    'Accept' => 'application/vnd.github.v3+json',
                ]
    ];

    protected $access_token;
    protected $github_url;

    protected $others = [
        'state' => 'closed',
        'since' => '2000-01-01%2000:00:00'
    ];

    protected $_entities = [
        'orgs' => [
            'laravel' => [
                'repos' => ['laravel',],
                'active' => true,
            ],
        ],
    ];

    protected $files;
    protected $fields = ['issues', 'pulls', 'commits'];
    protected $done = [];

    public function __construct()
    {
        $this->access_token = getenv('GITHUB_API_SECRET');
        $this->github_url = getenv('GITHUB_URL');
        $this->setDone();
    }


    /**
     * We can have something like this:
     *
     * {
    "laravel": {
    "repo": "https://api.github.com/laravel/laravel",
    "issues": "https://api.github.com/laravel/laravel/issues",
    "pulls": "https://api.github.com/laravel/laravel/pulls",
    "commits": "https://api.github.com/laravel/laravel/commits"
    }
    }
     */
    public function setDone()
    {
        $count = 0;
        foreach ($this->_entities['orgs'] as $key => $entity)
        {
            $this->done[$count]['repo'] = "{$key}";
            $this->done[$count]['url'] = "{$this->github_url}{$key}/{$entity['repos'][0]}";
            foreach ($this->fields as $k=>$field)
            {
                $this->done[$key][$field] = "{$this->github_url}{$key}/{$entity['repos'][0]}/{$field}";
            }
            $count++;
        }
        return $this;
    }

    public function getDone()
    {
        return $this->done;
    }

    protected function ___path($file, $project_name, $type='json')
    {
        return storage_path("app/{$project_name}/{$file}.{$type}");
    }

    protected function concat($_in, $_in2 = '', $separator = "?access_token=", $option = self::AFTER_STRING)
    {
        if($option === self::AFTER_STRING && !$_in2)
        {
            return $_in.$separator.$this->access_token;
        }

        if($option === self::AFTER_STRING && $_in2 )
        {
            return $_in.$separator.$_in2;
        }

        if($option === self::BEFORE_STRING && $_in2 )
        {
            return $_in2.$separator.$_in;
        }
    }

    protected function ping( $link, $headers = [], $default_response = ['body'], $method = 'GET')
    {
        $client = new GuzzleHttp\Client();
        $res = $client->request($method, $link, $headers);
        $result = $res;
        $_d_count = count($default_response);
        if($default_response[0] === 'body' && $_d_count === 1) {
            $result = $res->getBody();
        } elseif($default_response[0] === 'head'  && $_d_count === 1) {
            $result = $res->getHeaders();
        }
        return $result;
    }

    public function getContents($file_and_path)
    {
        if($response = file_get_contents(storage_path("{$file_and_path}")))
        {
            return $response;
        }
        return null;
    }

    function jsonToArray($_json)
    {
        return json_decode($_json, true);
    }

    function jsonToObject($_json)
    {
        return json_decode($_json);
    }

    function toArray($_var)
    {
        return (array)$_var;
    }

    function arrayToCollection($_array)
    {
       return collect($_array);
    }

    protected function respond($__attr, $code = 200, $transform = false)
    {
        if($transform) {
            return $this->respond($this->jsonToArray($__attr), $code);
        }
        return response()->json($__attr, $code);
    }

    protected function cutUrl($url, $cut)
    {
        return substr($url, 0, -$cut);
    }

    protected function cutLabelsUrl($url)
    {
        return $this->cutUrl($url, 7);
    }
}