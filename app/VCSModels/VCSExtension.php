<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use Illuminate\Database\Eloquent\Model;

class VCSExtension extends Model
{
    protected $primaryKey = 'Id';
    protected $table = "VCSExtensions";

    protected $fillable = ['Id', 'Extension', 'Type', 'isText', 'isXML', 'TypeId'];
}