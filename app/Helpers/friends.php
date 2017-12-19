<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 16/05/2017
 * Time: 16:04
 */


if (! function_exists('json_it')) {
    /**
     * json_encode a value
     *
     * @param mixed $_val
     * @return string
     */
    function json_it($_val)
    {
        return json_encode($_val);
    }
}

if (! function_exists('respond')) {
    /**
     * return a Json Response
     *
     * @param mixed $__attr
     * @param integer $code
     * @return string
     */
    function respond($__attr, $code = 200)
    {
        return \App\Utilities\Utility::respond($__attr, $code);
    }
}

if (! function_exists('dot_array')) {
    /**
     * Prepend a dot to each strings in an array
     *
     * @param array $array
     * @return array
     */
    function dot_array(array $array)
    {
        return \App\Utilities\Utility::dot($array);
    }
}