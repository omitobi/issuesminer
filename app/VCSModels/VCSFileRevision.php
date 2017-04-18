<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use Illuminate\Database\Eloquent\Model;

class VCSFileRevision extends Model
{
    protected $primaryKey = 'Id';
    protected $table = "VCSFileRevision";

    protected $fillable = [
        'Id',
        'Name',
        'FileId',
        'Date',
        'Comment',
        'PreviousRevisionId',
        'Alias',
        'ProjectLOC',
        'CommitterId',
        'Extension',
        'ExtensionId',
        'FileTypeId'
    ];

    public function vcs_text_file_revisions()
    {
        return $this->hasMany(VCSTextFileRevision::class, 'RevisionId', 'Id');
    }
}