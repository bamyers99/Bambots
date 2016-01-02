Bambots
=======

Bambots are a collection of [Wikipedia](https://en.wikipedia.org/) bots/tools.

* CategoryWatchlistBot is a category/template addition/removal watchlist generator.
* CleanupWorklistBot is a [WikiProject](https://en.wikipedia.org/wiki/Wikipedia:WikiProject) cleanup list generator.
* csv2wikitable is a tool for converting csv data to a wiki table.
* DatabaseReportBot generates miscellaneous reports.
* DataflowBot performs extracts, transforms, loads on row oriented data.
* InceptionBot is a WikiProject new article locator.
* PageTools is a tool for locating wikidata pages in other languages and for authority control id searchs.
* ReplicationLag is a tool for displaying database replication lag.
* WikidataBot is an unfinished wikidata report generator.
* WPMissingTmplBot is a WikiProject talk page missing banner locator.
* WPPageListBot is a WikiProject associated page locator.

Directory structure
===================

	/cache - Cache files
	/config - Config files
	/docs - Extended documentation
	/logs - Log files
	/output - Report output files
	/src - Source code
	/src/cli - Command line interfaces
	/src/l10n - Localizations
	/src/lib - Libraries
	/src/scripts - Scripts
	/src/test - Tests
	/src/web - Website

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
	
Command line programs
=====================
See /docs/crontab.txt for automated jobs that run on wmflabs.  
InceptionBot can run on labs, but is being run daily on an external vps with the following crontab:
	
	cd /bambots/src/cli; /usr/bin/php InceptionBot.php >>/bambots/logs/InceptionBot.log 2>&1

Manual jobs
-----------
All jobs must be started from /src/cli unless otherwise noted.

If CleanupWorklistBot fails or needs to be run manually the following is used:

	jsub -N CleanupWorklistBot -cwd -mem 768m php CleanupWorklistBot.php skipCatLoad

DatabaseReportBot BrokenSectionAnchors - run monthly on the 1st

	cd /cache/DatabaseReportBot ; cp ../../output/wikiviews . ; touch wikiviews
	cd /src/cli
	jsub -N DatabaseReportBot -cwd -mem 768m php DatabaseReportBot.php BrokenSectionAnchors
	copy /output/Wikipedia_Database_reports_Broken_section_anchors_1.txt to https://en.wikipedia.org/wiki/Wikipedia:Database_reports/Broken_section_anchors
	cd /src/scripts
	jsub clearcache.sh

DatabaseReportBot InvalidNavbarLinks - run monthly on the 1st

	jsub -N DatabaseReportBot -cwd -mem 768m php DatabaseReportBot.php InvalidNavbarLinks
	copy /output/Wikipedia_Database_reports_Invalid_Navbar_links.txt to https://en.wikipedia.org/wiki/Wikipedia:Database_reports/Invalid_Navbar_links
	cd /src/scripts
	jsub clearcache.sh

DatabaseReportBot StubTypeSizes - run monthly on the 1st

	jsub -N DatabaseReportBot -cwd -mem 768m php DatabaseReportBot.php StubTypeSizes
	copy /output to https://en.wikipedia.org/wiki/Wikipedia:WikiProject Stub sorting/Stub type sizes/data

Testing
=======
To run all tests:

	cd /test
	php runner.php

To run an individual test:

	cd /test
	php runner.php <path of test class>
	Example:
	php runner.php /com_brucemyers/test/Util/TestCSVString
	
Labs configuration
==================
git is run from /data/project/bambots/Bambots  
/data/project/bambots/public_html is linked to /data/project/bambots/Bambots/src/web  
/cache, /config, /logs directory permissions are set to drwxrws---  
