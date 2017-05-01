<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 6.03.17
 * Time: 13:43
 */

namespace App;


use App\VCSModels\VCSFileRevision;
use App\VCSModels\VCSTextFileRevision;
use Illuminate\Database\Eloquent\Model;

class Commit extends Model
{

    protected $guarded = [];
    public function vcs_text_file_revisions()
    {
        return $this->hasMany(VCSTextFileRevision::class, 'CommitId', 'id');
    }

    public function vcsFileRevisions()
    {
        return $this->hasMany(VCSFileRevision::class, 'CommitId', 'id');
    }
    /**
     * @param $search_attr
     * @param $attributes
     * @return mixed
     * @throws \Exception|$this
     */
    public static function findOrUpdate($search_attr, $attributes)
    {
        $models = self::where($search_attr)->get();
        $_level = count($models);
        if( $_level !== 1 ){
            throw new \Exception('Seems duplicate commit => \''.$search_attr['commit_sha'].'\' exists in \'commits\' table');
        }
        $model = $models->first();
        $result = $model->update($attributes);
        if(!$result){
            throw new \Exception('There is was an error updating the commit with commit_id '.$model->id);
        }
        return $model ;
    }
}