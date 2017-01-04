bunzip2 -c wikidatawiki*-pages-articles.xml.bz2 | ./xml2 | grep '"P279"\|"P31"\|"P360"' | php wdsubclass.php
