#gunzip -c /public/dumps/public/enwiki/20170420/enwiki-20170420-all-titles.gz | grep '/' | awk '{if ($1 != "0" && index($2, "/") != 1) print $1, substr($2, 1, index($2, "/") - 1)}' | uniq > /data/project/bambots/Bambots/data/basepagetitles
gunzip -c /public/dumps/public/enwiki/20170420/enwiki-20170420-all-titles.gz | grep '/' | awk '{if ($1 != "0" && index($2, "/") != 1) print $1, substr($2, 1, index($2, "/") - 1), $2}' > /data/project/bambots/Bambots/data/subpagetitles
LC_ALL=C sort -k 1,1n -k 2,2 /data/project/bambots/Bambots/data/subpagetitles > /data/project/bambots/Bambots/data/sortedsubpagetitles
rm /data/project/bambots/Bambots/data/subpagetitles
# sql enwiki
# TRUNCATE s51454__TemplateParamBot_p.enwiki_subpages;
# LOAD DATA LOCAL INFILE '/data/project/bambots/Bambots/data/basepagetitles' INTO TABLE s51454__TemplateParamBot_p.enwiki_basepages CHARACTER SET binary FIELDS TERMINATED BY ' ';
# DELETE FROM s51454__TemplateParamBot_p.enwiki_basepages WHERE pagetitle NOT IN (SELECT page_title FROM page WHERE page_namespace = namespace AND page_title = pagetitle AND page_is_redirect = 1);
# mysql --defaults-file="${HOME}"/replica.my.cnf -h enwiki.labsdb enwiki_p -B -e "SELECT * FROM s51454__TemplateParamBot_p.enwiki_basepages ORDER BY namespace, pagetitle" > redirbasepagetitles
# jsub -cwd php orphansubpages.php
# LOAD DATA LOCAL INFILE '/data/project/bambots/Bambots/data/subpages' INTO TABLE s51454__TemplateParamBot_p.enwiki_subpages CHARACTER SET binary;
