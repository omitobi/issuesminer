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

## License

The Lumen framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
