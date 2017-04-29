<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 28/04/2017
 * Time: 17:51
 */

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
class CollectionsUtilityMacroProvider extends ServiceProvider
{
    public function register()
    {
        require_once base_path() . '/collections.php';
    }

    public function boot()
    {

    }
}