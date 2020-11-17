bunzip2 -c /public/dumps/public/wikidatawiki/20201101/wikidatawiki*-pages-articles.xml.bz2 | xml2 | grep '^/mediawiki/page/revision/text=' | grep '"P31"' | php wdrelativeclass.php 1
