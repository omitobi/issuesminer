<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 27/03/2017
 * Time: 22:09
 */

namespace App\Utilities;


use App\Project;

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
     * @param mixed $total_developers
     */
    public function setTotalDevelopers($total_developers)
    {
        $this->total_developers = $total_developers;
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
}