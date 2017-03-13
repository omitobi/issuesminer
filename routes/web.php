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
$app->get('prs/commits/load', 'Issues\CommitsController@loadFromPrs');

//$app->get('/issues', 'Issues\IssuesController@resolve');
$app->get('/issues/load', 'Issues\IssuesController@load');
$app->get('/issues/prs/load', 'Issues\PrsController@load');


$app->post('/projects', 'Issues\ProjectsController@store');
$app->get('/projects', 'Issues\ProjectsController@fetch');