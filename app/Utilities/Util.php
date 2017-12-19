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

    /**
     * @param $paths
     * @param bool $repeat
     * @return array
     */
    function makeModules($paths, $repeat = false)
    {
        $result = [];

        if(!is_string($paths)) {
            foreach ($paths as $path)
            {
                if( !$last_slash = strrpos( $path, '/' )){
                    $result[] = '';
                } else{
                    $result[] = substr($path, 0,  $last_slash+1);
                }
            }
            return array_values(array_unique($result));
        }

        if( !$last_slash = strrpos( $paths, '/' )){
            return [''];
        }

        $result[] = substr($paths, 0,  $last_slash+1);
        return $result;
    }
}