<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 6.03.17
 * Time: 13:43
 */

namespace App;


use App\VCSModels\VCSTextFileRevision;
use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{
    public function vcs_text_file_revisions()
    {
        return $this->hasMany(VCSTextFileRevision::class, 'CommitId', 'id');
    }
}