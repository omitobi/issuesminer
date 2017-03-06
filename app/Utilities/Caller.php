<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 6.03.17
 * Time: 13:30
 */

namespace App\Utilities;


use GuzzleHttp;


class Caller
{
    protected $method;
    protected $url;
    protected $data;

    protected $result;

    public function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setAttributes()
    {
//        $URL_REF = parse_url($_SERVER['HTTP_REFERER']);
//        $URL_REF_HOST =   $URL_REF['host'];

//        $this->url = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
    }

    public function CallAPI($url = '', $method = '', $data = false, $headers = '')
    {
        if(!$url) { $url = $this->url;}
        if(!$method) { $method = $this->method;}
        if(!$data) { $data = $this->data;}

        $headers = ['User-Agent' => 'omitobi'];
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET',
            'https://api.github.com/repos/laravel/laravel/issues?state=closed',
            $headers);

        $result = $res->getBody();

        $this->setResult($result);
        return $this;
    }

    public function useGet()
    {
        $this->method = 'GET';
        return $this;
    }
}