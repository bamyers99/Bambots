find /public/dumps/public/other/mediawiki_history/2026-01/wikidatawiki -type f -name '*.tsv.bz2' -exec sh -c './bunzip2 -c "$1"' sh '{}' ';' | php navelgazertsv.php
