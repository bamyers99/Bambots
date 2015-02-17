BEGIN { FS = "\t"; OFS = "\t" }
{if ($2 == 0 && ($4 == 0 || $4 == 10)) print $3, $4}
