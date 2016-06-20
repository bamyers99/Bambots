BEGIN { FS = "="; OFS = "="; pagetype = 0 }
{if ($1 == "/mediawiki/page/ns" && $2 == "10") pagetype = 1}
{if ($1 == "/mediawiki/page/ns" && $2 != "10") pagetype = 0}
{if (pagetype == 1) print $0}
