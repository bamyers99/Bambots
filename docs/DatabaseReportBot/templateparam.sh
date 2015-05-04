bunzip2 -c articles.xml.bz2 | ./xml2 | grep '/mediawiki/page/ns=\|/mediawiki/page/id=\|/mediawiki/page/revision/text=' | awk -f templateparam.awk - >templateparam.txt
