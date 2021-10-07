#!/bin/bash
cat /dev/null >navelgazernoncomment.tsv
for filename in /public/dumps/public/wikidatawiki/20190401/wikidatawiki-*-pages-meta-history*.xml-*.7z; do
    echo "$filename"
    7zr e -so "$filename" | xml2 | grep '^/mediawiki/page/\(revision/\(contributor\|comment=\|text=\)\|title=\)' | php navelgazer.php parseMetaHistory
done
