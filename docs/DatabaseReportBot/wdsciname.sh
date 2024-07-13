bunzip2 -c /public/dumps/public/wikidatawiki/20240701/wikidatawiki*-pages-articles.xml.bz2 | xml2-0.5/xml2 | grep '^/mediawiki/page/revision/text=' | grep '"P225"' | php wdsciname.php
