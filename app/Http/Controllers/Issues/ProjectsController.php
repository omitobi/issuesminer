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
        $res = $this->ping($url, $this->headers );

        return $res;
    }

    public function fetch()
    {
        return Project::all();
    }
}