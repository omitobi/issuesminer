<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use Illuminate\Database\Eloquent\Model;

class ProjectDateRevision extends Model
{
    protected $primaryKey = 'Id';
    protected $table = "ProjectDateRevision";

    protected $guarded = [];

//    public function vcs_text_file_revisions()
//    {
//        return $this->hasMany(VCSTextFileRevision::class, 'RevisionId', 'Id');
//    }

    public function vcsFileType()
    {
        return $this->hasOne(VCSFiletype::class, 'Id', 'FiletypeId');
    }
//
//    public function vcsFileExtension()
//    {
//        return $this->hasOne(VCSExtension::class, 'Id', 'ExtensionId');
//    }
}