<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\ChurnCostsModels;


use Illuminate\Database\Eloquent\Model;

class ModuleChurnLevel extends Model
{
    protected $primaryKey = 'Id';
    protected $table = "projectmoduleachurnhistory";

    protected $guarded = [];

//    public function vcs_text_file_revisions()
//    {
//        return $this->hasMany(VCSTextFileRevision::class, 'RevisionId', 'Id');
//    }
}