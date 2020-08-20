bunzip2 -c /public/dumps/public/wikidatawiki/20200801/wikidatawiki*-pages-articles.xml.bz2 | xml2 | grep '^/mediawiki/page/revision/text=' | grep -v '"P279"\|"P31"' | php wdnoinstanceof.php
