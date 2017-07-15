<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 14/07/2017
 * Time: 23:17
 */

namespace App\Utilities;


use App\VCSModels\VCSProject;

trait Util
{
    public function getProject($id)
    {
        return VCSProject::where('name', $id)->orWhere('id', $id)->firstOrFail();
    }
}