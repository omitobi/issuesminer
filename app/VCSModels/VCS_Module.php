<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use Illuminate\Database\Eloquent\Model;

class VCS_Module extends Model
{
    protected $primaryKey = 'ModuleDateRevisionId';
    protected $table = "VCS_Modules";

    protected $guarded = [];

//    public function vcs_text_file_revisions()
//    {
//        return $this->hasMany(VCSTextFileRevision::class, 'RevisionId', 'Id');
//    }

    public function projecDateRevision()
    {
        return $this->hasOne(ProjectDateRevision::class, 'ModuleDateRevisionId', 'Id');
    }
//
    public function vcsFileRevisions()
    {
        return $this->hasMany(VCSFileRevision::class, 'CommitId', 'CommitId');
    }
}