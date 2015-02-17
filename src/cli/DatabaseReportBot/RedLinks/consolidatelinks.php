<?php
$ihndl = fopen($argv[1], 'r');
$ohndl = fopen($argv[2], 'w');

$prevtitle = null;
$prevcount = 0;
$prevtmplcount = 0;

while (! feof($ihndl)) {
	$line = fgets($ihndl);
	$line = rtrim($line);
	if (empty($line)) continue;

	list($title, $namespace) = explode("\t", $line);
	if ($prevtitle == null) $prevtitle = $title;

	if ($title != $prevtitle) {
		if ($prevcount >= 30 && ! $prevtmplcount && ! preg_match('!(_of_|_in_|_at_the_|\d{4})!', $prevtitle)) {
			fwrite($ohndl, "$prevtitle\t$prevcount\t$prevtmplcount\n");
		}

		$prevcount = 0;
		$prevtmplcount = 0;
		$prevtitle = $title;
	}

	++$prevcount;
	$prevtmplcount += (int)($namespace / 10);
}

if ($prevcount > 9) {
	fwrite($ohndl, "$prevtitle\t$prevcount\t$prevtmplcount");
}

fclose($ihndl);
fclose($ohndl);
