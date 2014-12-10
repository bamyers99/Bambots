bunzip2 -c wikidatawiki.bz2 | ./xml2 | grep '"P570"' | grep '"P569"' >people.txt
