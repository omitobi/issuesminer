<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use Illuminate\Database\Eloquent\Model;

class VCSFile extends Model
{
    protected $primaryKey = 'Id';
    protected $table = "VCSFile";

    protected $fillable = ['Id', 'Name'];
}