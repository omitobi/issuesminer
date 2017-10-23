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
use Illuminate\Support\Facades\Log;
use Monolog\Logger;

class Utility extends Controller
{
    const BEFORE_STRING = 0;
    const AFTER_STRING = 1;
    protected $unwanted_files;
    protected $log;

    protected $oo_langs = ['cpp', 'cs', 'php', 'java', 'cxx', 'hpp','js','d', 'fs', 'vb', 'ts', 'py'];
    protected $imp_langs = ['c','cpp', 'cxx', 'cs', 'php', 'java', 'cxx', 'h', 'hpp', 'js', 'py', 'rb', 'd', 'groovy', 'fs', 'fsx'];
    protected $texts = [
        'dtd',
        'py',
        'php',
        'java',
        'rb',
        'sgml',
        'txt',
        'wsdl',
        'xsd',
        'md',
        'readme',
        'cpp',
        'cs',
        'cxx',
        'hpp',
        'js',
        'c',
        'h',
        'cs',
        'php',
        'xml',
        'xls',
        'xsd'
    ];
    protected $xmls = ['xml', 'xsd', 'wsdl', 'xsl'];
    protected $types_  = [
        'h' => 'C',
        'cs' => 'C#',
        'cpp' => 'C++',
        'data' => 'Data',
        'dtd' => 'DTD',
        'groovy' => 'Groovy',
        'jpg' => 'Graphics',
        'png' => 'Graphics',
        'tiff' => 'Graphics',
        'xpm' => 'Graphics',
        'gif' => 'Graphics',
        'htm' => 'HTML',
        'js' => 'JavaScript',
        'xsd' => 'XML Schema',
        'sh' => 'Bash Script',
        'ods' => 'Open Document',
        'odt' => 'Open Document',
        'txt' => 'Plaintext',
        'py' => 'Python',
        'rb' => 'Ruby',
        'bin' => 'Binary',
        'class' => 'Binary',
        'dll' => 'Binary',
        'jar' => 'Binary',
        'o' => 'Binary',
        'exe' => 'Binary',
        'so' => 'Binary',
        'bat' => 'Command Script',
        'cmd' => 'Command Script',
        'dat' => 'Data',
        'csv' => 'Data',
        'in' => 'MakeFile',
        'am' => 'MakeFile',
        'php~' => 'PHP',
        'phpt' => 'PHP',
        'xslt' => 'XSL',
        'xslt,v' => 'XSL',
        '' => 'No extension',
    ];


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
    protected $guzzleclient;
    protected $fields = ['issues', 'pulls', 'commits'];
    protected $done = [];

    public function __construct()
    {
        $this->unwanted_files = ['jar', 'gif', 'jpg', 'png', 'gz', 'tar', 'zip'];
        $this->guzzleclient = new GuzzleHttp\Client();
        $this->access_token = getenv('GITHUB_API_SECRET');
        $this->github_url = getenv('GITHUB_URL');
        $this->setDone();
        $this->log = '';
    }


    /**
     * We can have something like this:
     *
     * <code type="json">{
    "laravel": {
    "repo": "https://api.github.com/laravel/laravel",
    "issues": "https://api.github.com/laravel/laravel/issues",
    "pulls": "https://api.github.com/laravel/laravel/pulls",
    "commits": "https://api.github.com/laravel/laravel/commits"
    }
    }
     * </code>
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

    protected function ping( $link, $headers = [], $default_response = ['body'], $method = 'GET', $public_client = false)
    {

        $client = $public_client ? $this->guzzleclient : (new GuzzleHttp\Client());

        $res = $client->request($method, $link, $headers);

        $result = $res;

        $_d_count = count($default_response);

        if ($default_response[0] === 'body' && $_d_count === 1) {
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

    /**
     * Encode the variable/value to a json string using json_encode()
     *
     * @param $_var
     * @param int $options
     * @param int $depth
     * @return string
     */
    function to_json($_var, $options = 0, $depth = 512)
    {
        return json_encode($_var, $options, $depth);
    }

    static function toArray($_var)
    {
        return (array)$_var;
    }

    function toCollection($_array)
    {
        if(is_array($_array))
            return collect($_array);
        return $this->toCollection($this->jsonToArray($_array));
    }

    static function stringToArray($__attr)
    {
        return is_string($__attr) ? [$__attr] : $__attr;
    }

    public static function respond($__attr, $code = 200)
    {
        if(is_string($__attr))
            return self::respond(self::stringToArray($__attr), $code);

        if(is_object($__attr))
            return self::respond(self::toArray($__attr), $code);

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


    /**
     * Mass (bulk) insert or update on duplicate for Laravel 4/5
     *ref: http://stackoverflow.com/a/27593831/5704410
     * src: https://gist.github.com/RuGa/5354e44883c7651fd15c
     * insertOrUpdate([
     *   ['id'=>1,'value'=>10],
     *   ['id'=>2,'value'=>60]
     * ]);
     *
     *
     * @param array $rows
     */
    public function insertOrUpdate(array $rows, $table){
//        $table = \DB::getTablePrefix().with(new $this->table)->getTable();
//        $table = self::getTable();


        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    ).')';
            } , $rows )
        );

        $updates = implode( ',',
            array_map( function( $value ) { return "$value = VALUES($value)"; } , array_keys($first) )
        );


        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::statement( $sql );
    }


    /**
     * @param array $array
     * @return array
     */
    public static function dot(array $array)
    {
        return array_map(function ($value){
            return '.'.$value;
        }, $array);
    }

}