<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use Illuminate\Database\Eloquent\Model;

class VCSFiletype extends Model
{
    protected $primaryKey = 'Id';
    protected $table = "VCSFiletypes";

    protected $fillable = ['Id', 'Type', 'isText', 'isXML', 'isImperative', 'isOO'];
}