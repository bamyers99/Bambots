BEGIN { FS = "\t"; OFS = "\t" }
{if ($7 == "subcat") print $1, $2}
