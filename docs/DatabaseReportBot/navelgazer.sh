gunzip -c wikidatawiki*-stub-meta-history.xml.gz | ./xml2 | grep '/mediawiki/page/revision/contributor/username=\|/mediawiki/page/revision/comment=' | awk -f navelgazer.awk - >navelgazer.txt
LC_ALL=C sort navelgazer.txt | LC_ALL=C uniq -c | sed -r 's/ *([^ ]+) ([^\t]+)\t(.*)/\2\t\3\t\1/' >navelgazer.tsv
