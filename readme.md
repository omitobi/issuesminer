# Issues Miner

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

## Issues Retrieval:

- is_pr "Fix" in Title, and is_closed, is_merged:
https://api.github.com/search/issues?q=repo:FortAwesome/Font-Awesome+is:pr+is:closed+fix+in:title+is:merged+updated:%3C2017-05-01
https://api.github.com/search/issues?q=repo:jquery/jquery+is:pr+is:closed+fix+in:title+is:merged

- "bugfix" in Title, is_closed, is_merged
https://api.github.com/search/issues?q=repo:jquery/jquery+is:pr+is:closed+bugfix+in:title+is:merged

- "bug" in Title, is_closed, is_merged
https://api.github.com/search/issues?q=repo:jquery/jquery+is:pr+is:closed+bug+in:title+is:merged

- "error" in Title, is_closed, is_merged
https://api.github.com/search/issues?q=repo:jquery/jquery+is:pr+is:closed+error+in:title+is:merged
