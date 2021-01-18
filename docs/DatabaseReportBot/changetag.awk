BEGIN { FS = "\t"; OFS = "\t" }
{if ($4 != "NULL") print $4, $6}
