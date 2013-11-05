Bambots
=======

Bambots are a collection of Wikipedia bots.

* InceptionBot is a [Wikipedia](https://en.wikipedia.org/) [WikiProject](https://en.wikipedia.org/wiki/Wikipedia:WikiProject) new article locator.
* WPPageListBot is Wikipedia WikiProject associated page locator.

Directory structure
===================

	/cache - Cache files
	/config - Config files
	/logs - Log files
	/src - Source code

Config file
===========

filename - *.properties where * = bot name or testbot

	wiki.username=
	wiki.password=
	wiki.url=https://en.wikipedia.org/w/api.php
	wiki.pagefetchincrement=50
	wiki.recentchangesincrement=500
	# cache.dir Use {basedir} to start in the project root directory
	cache.dir={basedir}/cache
	cache.expirydays=14
	InceptionBot.lastrun=20131029000000
	InceptionBot.historydays=15
    InceptionBot.outputdir={basedir}/results
    # InceptionBot.outputtype 'file', 'wiki'
    InceptionBot.outputtype=file
    # InceptionBot.ruletype 'active', 'custom', 'all'
    InceptionBot.ruletype=custom
    InceptionBot.customrule=HipHop
	