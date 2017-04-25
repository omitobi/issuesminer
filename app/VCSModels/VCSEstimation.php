<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use Illuminate\Database\Eloquent\Model;

class VCSEstimation extends Model
{
    protected $primaryKey = 'Id';
    protected $table = "VCSEstimations";

    protected $guarded = [];
//    protected $fillable = ['Id', 'Name'];

    public function wheresIn($keys, $attributes, $model = null, $result = null){

        foreach ($keys as  $key)
        {
            $all_values = array_pluck($attributes, $key);

            if(!$model)
                $model = $this->whereIn($key, $all_values);
            else
                $model->whereIn($key, $all_values);
        }

        return $model;

    }
}