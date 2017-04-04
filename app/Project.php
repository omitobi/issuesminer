<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 6.03.17
 * Time: 13:43
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'identifier',
        'organization_name',
        'name',
        'type',
        'language',
        'description',
        'homepage',
        'api_url',
        'web_url',
        'commits_url',
        'issues_url',
        'prs_url',
        'organization_name',
        'date_created',
        'default_branch'
    ];

    public function details()
    {
        return $this->hasOne(ProjectDetail::class);
    }

    public function developers()
    {
        return $this->hasMany(Developer::class);
    }
}