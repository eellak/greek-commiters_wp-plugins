Synopsis
This project deals with the aggregation, in a presentable and queryable manner, of the developers that have a residence* in the greek territory and contribute in open source projects in Github.

Location
http://opensource.ellak.gr/github_contributor

Motivation
The motivation behind this project is the wish that greece located developers have a reference of the community and especially a locality reference, so that the collaboration can be easily promoted to the physical level.

Documentation
The project consists of four plugins for the wordpress platform that modularize the functionality into four section
1. Declare the Custom Post types and the Tags that will accomodate the contributor entries
2. The Query Handler functionality that handles the POST request that the user submits to the wordpress platform when selecting a city query or a different sorting parameter.
3. The deletion plugin that removes all the contributor entries in a batch process from the wordpress platform.
4. The synch plugin that takes as input the json files that are retrieved by the <a href="https://github.com/eellak/greek-commiters">greek-commiters</a> scripts and imports them as Wordpress posts.

Licence
GNU-GPLv3
