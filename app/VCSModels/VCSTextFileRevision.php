<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use Illuminate\Database\Eloquent\Model;

class VCSTextFileRevision extends Model
{
    protected $primaryKey = 'RevisionId';
    protected $table = "VCSTextFileRevision";

    protected $fillable = [
        'RevisionId',

        'CodeChurnLines',
        'AddedCodeLines',
        'RemovedCodeLines',
        'LinesOfCode',

        'ContentsU',
        'CompressedContents',

        'status',

        'CommitId',
        'FileId',
        'ProjectId'
    ];
}