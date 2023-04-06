BEGIN { FS = "\t"; OFS = "\t" }
{if ($4 != "NULL" && $6 != "708") print $4, $6}
