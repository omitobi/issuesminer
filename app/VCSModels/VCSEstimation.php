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

//    protected $fillable = ['Id', 'Name'];
}