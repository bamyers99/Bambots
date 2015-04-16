Bambots
=======

Bambots are a collection of [Wikipedia](https://en.wikipedia.org/) bots/tools.

* CategoryWatchlistBot is a category/template addition/removal watchlist generator.
* CleanupWorklistBot is a [WikiProject](https://en.wikipedia.org/wiki/Wikipedia:WikiProject) cleanup list generator.
* InceptionBot is a WikiProject new article locator.
* ReplicationLag is a tool for displaying database replication lag.
* WPMissingTmplBot is a WikiProject talk page missing banner locator.
* WPPageListBot is a WikiProject associated page locator.

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
    InceptionBot.erroremail=a@b.com
    # InceptionBot.currentproject (for restart capability)
    InceptionBot.currentproject=
    # InceptionBot.currentend (for restart capability)
    InceptionBot.currentend=
	
    CleanupWorklistBot.outputdir={basedir}/results
    # CleanupWorklistBot.outputtype 'file', 'wiki'
    CleanupWorklistBot.outputtype=file
    # CleanupWorklistBot.ruletype 'active', 'custom', 'all'
    CleanupWorklistBot.ruletype=custom
    CleanupWorklistBot.customrule=WikiProject_Michigan
    CleanupWorklistBot.erroremail=a@b.com
    # CleanupWorklistBot.currentproject (for restart capability)
    CleanupWorklistBot.currentproject=
    CleanupWorklistBot.htmldir=/home/html/cwb/
    CleanupWorklistBot.urlpath=https://tools.wmflabs.org/bambots/cwb/
    CleanupWorklistBot.enwiki_host=
    CleanupWorklistBot.tools_host=
    CleanupWorklistBot.labsdb_username=
    CleanupWorklistBot.labsdb_password=
	