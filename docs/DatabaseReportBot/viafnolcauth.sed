# gunzip -c viaf.rdf.gz | sed -n -f viafnolcauth.sed >viafnolcauth.rdf
s!^\([0-9]\+\).*<schema:sameAs><rdf:Description rdf:about="http://id.loc.gov/authorities/names/\([^"]\+\)".*$!\1\t\2!p
