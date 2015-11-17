BEGIN { FS = "\t"; OFS = "\t" }
{if ($2 == 14) print $1, $3}
