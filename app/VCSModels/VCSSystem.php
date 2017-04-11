<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use Illuminate\Database\Eloquent\Model;

class VCSSystem extends Model
{
    protected $primaryKey = 'Id';
    protected $table = "VCSSystem";

    public function VCSProjects()
    {
        return $this->hasMany(VCSProject::class, 'SystemId', 'Id');
    }
}