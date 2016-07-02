BEGIN { FS = "="; OFS = "\t"; username = "" }
{if ($1 == "/mediawiki/page/revision/contributor/username") {
	username = $0
	sub(/\/mediawiki\/page\/revision\/contributor\/username=/, "", username)
	}
}
{if ($1 == "/mediawiki/page/revision/comment") {
	comment = $0
	sub(/\/mediawiki\/page\/revision\/comment=/, "", comment)
	if (index(comment, "claim-create") != 0 && match(comment, /\[\[Property:P[0-9]+/) != 0) {
		print username, substr(comment, RSTART + 12, RLENGTH - 12)
		}
	}
}
