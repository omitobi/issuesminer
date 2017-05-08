<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 07/05/2017
 * Time: 01:26
 */

namespace App\Utilities;


use App\VCSModels\VCSProject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Cachetility extends Utility
{
    protected $cacheExpiry;

    public function __construct(Carbon $carbon = null)
    {
        $carbon = $carbon ?: Carbon::now();
        $this->cacheExpiry = $this->cacheExpiry ? : $carbon->addMinutes(10);
        parent::__construct();
//        if(function_exists('getProject')){
//
//            forward_static_call('getProject', get)
//        }
    }

    public static function getDevelopersCount($project, $expiry = null)
    {
        $expiry = self::setExpiry($expiry);
        return Cache::remember('vcs_dev_count_project_'.$project->Id, $expiry, function () use ($project) {
                return $project->commits()
                    ->select('id','author_email', 'date_committed')
                    ->distinct()
                    ->count('author_email');
            });
    }


    public static function countDevelopers($project, $langs, $lang_type, $expiry = null)
    {
        $expiry = self::setExpiry($expiry);
        return Cache::remember('vcs_dev_count_project_'.$project->Id.'_lang_'.$lang_type, $expiry, function () use ($project, $langs) {
            return $project->vcsFileRevisions()
                ->select('AuthorEmail')
                ->whereIn('Extension', parent::dot($langs))
                ->distinct()
                ->count('AuthorEmail');
        });
    }



    protected static function setExpiry($expiry = 0)
    {
        $expiry = $expiry ? : Carbon::now()->addMinutes(60)->toDateTimeString();
        return $expiry;
    }

    public static function getProject($project_id, $expiry = 0)
    {
        $expiry = self::setExpiry($expiry);
        return Cache::remember('vcs_project_'.$project_id, $expiry, function () use ($project_id){
               return VCSProject::seek($project_id);
            });
    }

    public static function clear($key, $action = 'delete')
    {
        if(Cache::has($key)) {
            if($action !== 'pull' ){
                return Cache::forget($key);
            }
            return Cache::pull($key);
        }
        throw new \Exception('The key '.$key.' does not exist in cache');
    }

//    public function __call($name, $arguments)
//    {
//        return forward_static_call([self::class, $name], ...$arguments);
////        return call_user_func([self::class, $name], ...$arguments);
//    }

    public static function __callStatic($name, $args)
    {
/*        $args['expiry'] = Carbon::now()->addMinutes(5);*/

        $arg = count($args) === 0 ? 1 : $args[0];
        if($name === 'getProject'){
            return forward_static_call([Cachetility::class, 'getProject'], ...$args);
        }
    }
}