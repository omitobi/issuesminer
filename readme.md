# Issues Miner

## Introduction

This repo contains the code base for the Laravel (Lumen) project use to retrieve Commits data from Github API Version 3 and also re-organize the data to be used in the Thesis titled: 'The relationship between Module size, Alternative cost and Bugs' as fulfilment of the requirement of obtaining Masters' of Software Engineering at the University of Tartu (August 2018)

## Other projects related

- Front-end: https://github.com/omitobi/issuesminer_front
- Analysis (R) scripts and data: https://github.com/omitobi/issuesminerscripts

## The following is considered in their order
```php
$app->get('/projects', 'Issues\ProjectsController@fetch');

//1. Create/Store: project
$app->post('/projects', 'Issues\ProjectsController@store');

//2. Load: project->issues that is, load all issues from project [Requires: project_name] [Optional: page(number) based on the next_page field in the response]
$app->get('/issues/load', 'Issues\IssuesController@load');

//3. Load: issues->prs
$app->get('/issues/prs/load', 'Issues\PrsController@load');

//4. Load: prs->commits
$app->get('prs/commits/load', 'Issues\CommitsController@loadFromPrs');

//5. Load: commits->file_changes
$app->get('commits/files/load', 'Issues\CommitsFilesController@loadFromCommits');
```

## Load Commits from a project:

```$xslt
http://localhost:8001/commits?project_name=atom&sort=created&direction=asc&per_page=100&until=2017-03-21T22:32:43Z
```

Parameters required:
```$xslt
project_name,
sort [created, updated],
direction [asc, desc],
per_page [max: 100],
until  [given: last datetimestamp .eg 2017-03-21T22:32:43Z]
```

## Retrieve Issues from a project:

### Normal

Depending on the labels, issue status, and the max date of 2017-03-31

##### Normal on App's end 'issues/load'

```text
localhost:8001/issues/load?project_name=php-src&state=closed&sort=created&direction=asc&since=2000-01-01T00:00:01Z&per_page=100&labels=Bugfix
```

### Otherwise

#### Factors considered based on Github Search API 
When the following conditions are considered, that is in order to find more relevant bugs/issues reports then the title of the PR/issues is considered:

- is_pr "Fix" in Title, and is_closed, is_merged:
https://api.github.com/search/issues?q=repo:FortAwesome/Font-Awesome+is:pr+is:closed+fix+in:title+is:merged+updated:%3C2017-05-01
https://api.github.com/search/issues?q=repo:jquery/jquery+is:pr+is:closed+fix+in:title+is:merged

- "bugfix" in Title, is_closed, is_merged
https://api.github.com/search/issues?q=repo:jquery/jquery+is:pr+is:closed+bugfix+in:title+is:merged

- "bug" in Title, is_closed, is_merged
https://api.github.com/search/issues?q=repo:jquery/jquery+is:pr+is:closed+bug+in:title+is:merged

- "error" in Title, is_closed, is_merged
https://api.github.com/search/issues?q=repo:jquery/jquery+is:pr+is:closed+error+in:title+is:merged

##### From the App's end `issues/load` endpoint:

```text
localhost:8001/issues/load?project_name=react&page=19
```

##### Replace $issues_query_url in `IssuesController` with: 
```php
$issues_query_url = 'https://api.github.com/search/issues?q=repo:facebook/react+is:pr+is:closed+fix+in:title+is:merged+created:%3C2017-03-31+sort%3Acreated-asc&'.$_query;
```

##### Then `$in_issues`  is replaced with:
 ```php
$in_issues = $this->jsonToArray($_body)['items'];
```

## Retrieve Pull Requests (PRs) from issues

### Normal

```text
http://localhost:8001/issues/prs/load?project_name=react
```

After retrieval the `pr_retrieved` column in the `issue` table is filled the hash/id of the pr

## Retrieve (issues_commits) Commits from PRs

### Normal

```text
http://localhost:8001/prs/commits/load?project_name=react
```

## Retrieve commits_file_changes from issues_commits

### Normal

```text
http://localhost:8001/commits/files/load?project_name=react
```

## Authors

- Oluwatobi Samuel Omisakin [@omitobi](https://github.com/omitobi)

## Permissions

The code base and its related projects cannot be used unless by written and approved permission of the Author
