<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

//php -S localhost:8000 -t ./public
//$app->configure('cors');
$app->get('/', function () use ($app) {
    return $app->version();
});


$app->get('/projects', 'Issues\ProjectsController@fetch');

//1. Create/Store: project
$app->post('/projects', 'Issues\ProjectsController@store');


//2. Load: project->issues
$app->get('/issues/load', 'Issues\IssuesController@load');

//3. Load: issues->prs
$app->get('/issues/prs/load', 'Issues\PrsController@load');

//4. Load: prs->commits
$app->get('prs/commits/load', 'Issues\CommitsController@loadFromPrs');

//5. Load: commits->file_changes
$app->get('commits/files/load', 'Issues\CommitsFilesController@loadFromCommits');



//load all commits
$app->get('commits', 'General\CommitsController@load');
$app->get('commits/updates', 'General\CommitsController@updateAll');
$app->get('commits/reload', 'General\CommitsController@updateCommitsWithoutEmail');
$app->get('commits/untouch', 'General\CommitsController@untouch_commits');


//$app->get('/issues', 'Issues\IssuesController@resolve');

$app->get('/projects/details', 'General\ProjectDetailsController@load');
$app->post('/developers', 'General\DevelopersController@load');


//VCS Routes
$app->post('/vcs/projects', 'VCS\ProjectsController@loadAll');
$app->post('/vcs/files', 'VCS\VCSFilesController@save');
$app->get('/vcs/fileextensions', 'VCS\VCSFilesController@sortExtensions');
$app->post('/vcs/filetypes', 'VCS\VCSFilesController@sortFileTypes');
$app->get('/vcs/filerevisions', 'VCS\VCSFileRevisionsController@sortRevisions');


$app->get('/vcs/estimations', 'VCS\VCSEstimationsController@loadAll');
$app->get('/vcs/revisions/dates', 'VCS\VCSEstimationsController@loadRevisionDates');

$app->get('/vcs/modules', 'VCS\VCSModulesController@loadAll');
$app->get('cachetests', 'VCS\CachesController@test');


$app->get('/tester/projects/{project}', 'VCS\TestersController@updateVCS_ModulesWithDate');


//costs and bugfixes

$app->get('/costsfixes/{project}', 'Costs\AlternativeCostController@mergeCostsAndIssues');
$app->get('/locs/{project}', 'Costs\LOCCounterController@load');
