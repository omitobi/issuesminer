<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 25/03/2017
 * Time: 09:01
 */

namespace App\Http\Controllers\General;


use App\Utilities\Utility;

class CommitsController extends Utility
{
    public function load()
    {
        return ['yes'];
    }
}