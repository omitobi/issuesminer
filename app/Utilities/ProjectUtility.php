<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 27/03/2017
 * Time: 22:09
 */

namespace App\Utilities;


use App\Developer;
use App\Project;
use Illuminate\Database\Eloquent\Model;

class ProjectUtility extends Utility
{
    protected  $project;

    protected  $developers;

    protected  $issues_labels;
    protected  $file_types;
    protected  $languages;
    protected  $total_developers;
    protected  $total_commits;
    protected  $total_issues;
    protected  $total_bug_issues;
    protected  $total_closed_bug_issues;
    protected  $total_created_files;
    protected  $total_modified_files;
    protected  $total_deleted_files;
    protected  $total_deletions;
    protected  $total_additions;

    public function __construct()
    {
        //todo: setup ETag or Last-Modified for saving rate limit
        $this->headers['headers']['Last-Modified'] = 'Tue, 28 Mar 2017 07:24:36 GMT';
        return parent::__construct();
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }


    /**
     * @return mixed
     */
    public function getDevelopers()
    {
        return $this->developers;
    }

    /**
     * @param mixed $developers
     */
    public function setDevelopers($developers)
    {
        $this->developers = $developers;
    }


    /**
     * @return mixed
     */
    public function getIssuesLabels()
    {
        return $this->issues_labels;
    }

    /**
     * @param mixed $labels_url
     * @return $this
     */
    public function setIssuesLabels($labels_url ='')
    {
        if(!$labels_url) { $labels_url = $this->project->labels_url; }

        if(!$labels = $this->alreadySaved($this->project, 'issues_label')) {
            $ping = $this->ping(
                $this->concat($this->cutLabelsUrl($labels_url)),
                $this->headers
            );
            $this->issues_labels = $this->arrayToCollection($this->jsonToArray($ping))->pluck('name');
            return $this;
        }
        $this->issues_labels = $labels;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileTypes()
    {
        return $this->file_types;
    }

    /**
     * @param mixed $file_types
     */
    public function setFileTypes($file_types)
    {
        $this->file_types = $file_types;
    }

    /**
     * @return mixed
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param mixed $languages_url
     *
     * @return $this
     */
    public function setLanguages($languages_url = '')
    {
        if(!$languages_url) { $languages_url = $this->project->languages_url; }

        if(!$languages = $this->alreadySaved($this->project, 'languages')) {
            $ping = $this->ping(
                $this->concat($languages_url),
                $this->headers
            );
            $this->languages = $this->jsonToArray($ping);
            return $this;
        }
        $this->languages = $languages;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalDevelopers()
    {
        return $this->total_developers;
    }

    /**
     * Also known as contributors?
     * @param mixed $contributors_url
     *
     * @return $this
     */
    public function setTotalDevelopers($contributors_url = '', $page='')
    {

        if(!$contributors_url) { $contributors_url = $this->project->contributors_url; }

        if(!$developers = $this->alreadySaved($this->project, 'total_developers')) {
            $ping = $this->ping(
                $this->concat($this->concat($contributors_url), 'per_page=100'.$page, '&'),
                $this->headers,
                ['body', 'head']
            );
            $this->setDevelopers( collect($this->jsonToArray($ping->getBody())) );

            $_next = [];
            if(count($header_ = $ping->getHeader('Link')))
            {

                $links = explode(',', $header_[0]);

//            return $parsed = Psr7\parse_header($ping->getHeader('Link'));
                if (($_fs = strpos($links[0], '<')) === 0)
                    $_next['page'] =  substr($links[0], $_fs+1, strpos($links[0],'>')-1);

                if (($_ls = strpos($links[1], '<')) === 1)
                    $_next['last'] =  substr($links[1], $_ls+1, strpos($links[1],'>')-2);

                if (($_fsn = strpos($links[0], "&page=")) > 0)
                    $_next['next_page'] = substr($links[0], $_fsn + 6, -13);

                if (($_lsn = strpos($links[1], "&page=")) > 0)
                    $_next['last_page'] = substr($links[1], $_lsn+6, -13);

            }

            $this->next_ = $_next;
            $this->saveDevelopers($this->developers);
            return $this;
        }
        $this->developers = $developers;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalCommits()
    {
        return $this->total_commits;
    }

    /**
     * @param mixed $total_commits
     */
    public function setTotalCommits($total_commits)
    {
        $this->total_commits = $total_commits;
    }

    /**
     * @return mixed
     */
    public function getTotalIssues()
    {
        return $this->total_issues;
    }

    /**
     * @param mixed $total_issues
     */
    public function setTotalIssues($total_issues)
    {
        $this->total_issues = $total_issues;
    }

    /**
     * @return mixed
     */
    public function getTotalBugIssues()
    {
        return $this->total_bug_issues;
    }

    /**
     * @param mixed $total_bug_issues
     */
    public function setTotalBugIssues($total_bug_issues)
    {
        $this->total_bug_issues = $total_bug_issues;
    }

    /**
     * @return mixed
     */
    public function getTotalClosedBugIssues()
    {
        return $this->total_closed_bug_issues;
    }

    /**
     * @param mixed $total_closed_bug_issues
     */
    public function setTotalClosedBugIssues($total_closed_bug_issues)
    {
        $this->total_closed_bug_issues = $total_closed_bug_issues;
    }

    /**
     * @return mixed
     */
    public function getTotalCreatedFiles()
    {
        return $this->total_created_files;
    }

    /**
     * @param mixed $total_created_files
     */
    public function setTotalCreatedFiles($total_created_files)
    {
        $this->total_created_files = $total_created_files;
    }

    /**
     * @return mixed
     */
    public function getTotalModifiedFiles()
    {
        return $this->total_modified_files;
    }

    /**
     * @param mixed $total_modified_files
     */
    public function setTotalModifiedFiles($total_modified_files)
    {
        $this->total_modified_files = $total_modified_files;
    }

    /**
     * @return mixed
     */
    public function getTotalDeletedFiles()
    {
        return $this->total_deleted_files;
    }

    /**
     * @param mixed $total_deleted_files
     */
    public function setTotalDeletedFiles($total_deleted_files)
    {
        $this->total_deleted_files = $total_deleted_files;
    }

    /**
     * @return mixed
     */
    public function getTotalDeletions()
    {
        return $this->total_deletions;
    }

    /**
     * @param mixed $total_deletions
     */
    public function setTotalDeletions($total_deletions)
    {
        $this->total_deletions = $total_deletions;
    }

    /**
     * @return mixed
     */
    public function getTotalAdditions()
    {
        return $this->total_additions;
    }

    /**
     * @param mixed $total_additions
     */
    public function setTotalAdditions($total_additions)
    {
        $this->total_additions = $total_additions;
    }

    public function alreadySaved($project, $field)
    {
        if(!$details = $project->details)
        {
            return false;
        }

        if($data = $details->{$field})
        {
            return $data;
        }

        return false;
    }

    public function saveDevelopers($developers)
    {
        Model::unguard();
        $devs = [];
        foreach ($developers as $key => $developer)
        {

            $devs['name'] = $developer['login'];
            $devs['identifier'] = $developer['id'];
//            $dev->email = $developer->login;
            $devs['api_url'] = $developer['url'];
            $devs['web_url'] = $developer['html_url'];
            $devs['total_contributions'] = $developer['contributions'];
//            $dev->date_created = $developer->contributions;

            $this->project->developers()->updateOrCreate(['name' => $developer['login']], $devs);
        }

        Model::reguard();

        return true;
    }
}