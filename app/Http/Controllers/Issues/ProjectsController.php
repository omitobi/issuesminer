<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 8.03.17
 * Time: 21:03
 */

namespace App\Http\Controllers\Issues;


use App\Http\Controllers\Controller;
use App\Project;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    public function store(Request $request)
    {
        $url = $request->get('url');

        return $url;
    }

    public function fetch()
    {
        return Project::all();
    }
}