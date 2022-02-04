<?php
/**
 Copyright 2017 Myers Enterprises II

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.

 Dump CurrencyConverter wikidata gadget json files.

 https://www.x-rates.com/table/?from=GBP&amount=1
 INSERT INTO wikidatawiki_p.`currency_rates` VALUES (2019,1.837305,4.687543,1.819494,8.865283,8.525941,1.144069,91.731511,138.771977,150.393250,25.778737,1.952902,11.078723,4.809790,81.286671,16.579594,11.763961,1.347300,5.647245,1,1.408076,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL)
 https://www.imf.org/external/datamapper/PCPIPCH@WEO/WEOWORLD/VEN
 https://data.oecd.org/price/inflation-cpi.htm yearly
 mysqldump -u 'user' -p 'database' currency_curs currency_rates currency_mults >currency_rates.sql

 */

$count = 0;
$data = array();
$current_year = 0;
$ISOs = array();

if ($_SERVER['argc'] < 6) {
	echo "Usage: php dumpcurrency.php 'host' 'database' 'user' 'password' 'outputdir'\n";
	exit;
}

$host = $_SERVER['argv'][1];
$database = $_SERVER['argv'][2];
$user = $_SERVER['argv'][3];
$password = $_SERVER['argv'][4];
$outdir = $_SERVER['argv'][5];

$dbh_wiki = new PDO("mysql:host={$host};dbname={$database};charset=utf8mb4", $user, $password);
$dbh_wiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sth = $dbh_wiki->query('SELECT * FROM currency_curs');
while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
	$data[$row['QID']] = processCurrency($row, $current_year);
	$ISOs[$row['ISO']] = $row['QID'];
	++$count;
}

$sth->closeCursor();

$data['current_year'] = (int)$current_year;
$data['ISOs'] = $ISOs;

file_put_contents("$outdir/currency.json", json_encode($data));

echo "Processed $count currencies\n";

exit;

/**
 * Process a currency
 *
 * @param array $row
 * @param int $current_year
 */
function processCurrency($row, &$current_year)
{
	global $dbh_wiki, $outdir;
	$qid = $row['QID'];
	$iso = $row['ISO'];
	$rate_field = $row['rate_name'];
	$inflat_field = $row['inflat_name'];

	$data = array();
	$mults = array();

	// Get the multipliers
	$sth = $dbh_wiki->query("SELECT * FROM currency_mults WHERE ISO = '$iso'");
	while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
		$mults[] = array('start' => (int)$row['start_year'], 'end' => (int)$row['end_year'], 'mult' => (int)$row['multiplier']);
	}

	$data['multipliers'] = $mults;

	$sth->closeCursor();
	$rates = array();

	// Get the conversion rates
	$sth = $dbh_wiki->query("SELECT year, $rate_field AS rate FROM currency_rates WHERE $rate_field IS NOT NULL ORDER BY year");
	while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
		$rates[$row['year']] = round($row['rate'], 3);
	}

	$data['rates'] = $rates;

	$sth->closeCursor();
	$cpis = array();
	$cpi = 100.0;

	// Calculate the yearly Consumer Price Index
	$sth = $dbh_wiki->query("SELECT year, $inflat_field AS rate FROM currency_rates WHERE $inflat_field IS NOT NULL ORDER BY year");
	while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
		$cpi += $cpi * $row['rate'] / 100;
		$cpis[$row['year']] = (int)$cpi;
		$current_year = $row['year'];
	}

	$data['cpis'] = $cpis;

	$sth->closeCursor();

	//file_put_contents("$outdir/$qid.json", json_encode($data));
	return $data;
}
