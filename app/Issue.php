<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 6.03.17
 * Time: 13:43
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $table = 'issues';

    public function fileChanges()
    {
        return $this->hasMany(CommitsFileChange::class);
    }
}