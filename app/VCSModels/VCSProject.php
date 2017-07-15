<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 11/04/2017
 * Time: 15:51
 */

namespace App\VCSModels;


use App\Commit;
use Illuminate\Database\Eloquent\Model;

class VCSProject extends Model
{
    protected $primaryKey = 'Id';
    protected $table = "VCSProject";

    public function VCSFiles()
    {
        return $this->hasMany(VCSFile::class, 'ProjectId', 'Id');
    }

    public function vcsFileRevisions()
    {
        return $this->hasMany(VCSFileRevision::class, 'ProjectId', 'Id');
    }
    public function vcsModules()
    {
        return $this->hasMany(VCS_Module::class, 'ProjectId', 'Id');
    }

    public function projectDateRevisions()
    {
        return $this->hasMany(ProjectDateRevision::class, 'ProjectId', 'Id');
    }

    public function commits()
    {
        return $this->hasMany(Commit::class, 'project_id', 'Id');
    }

    public function scopeSeek($query, $project_id)
    {
        return $query->where('Id', $project_id)->orWhere('Name',$project_id)->first();
    }
}