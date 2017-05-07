<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:48
 */
namespace App\Http\Controllers\VCS;



use App\Http\Controllers\Controller;
use App\Utilities\Cachetility;


class CachesController extends Controller
{
    protected  $cachetility;

    function __construct(Cachetility $cachetility)
    {
        $this->cachetility = $cachetility;
    }

    public function test()
    {
        $project = $this->cachetility->getProject(1);

        $all_dev_count = $this->cachetility->getDevelopersCount($project);


//        Cache::add('all_dev_projec_'.$project->Id, $all_dev_count, 5);
//        $value = Cache::get('number');
        return [$all_dev_count];
    }
}