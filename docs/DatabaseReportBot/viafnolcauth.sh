bunzip2 -c wikidata-pages-articles.xml.bz2 | ./xml2 | grep '"P214"' | grep -v '"P244"' >viafnolcauth.txt
