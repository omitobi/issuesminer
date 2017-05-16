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