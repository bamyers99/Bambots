<?php
/**
 Copyright 2015 Myers Enterprises II

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

// mysql --defaults-file=~/replica.my.cnf -h enwiki.labsdb s51454__tmpl_params_p <templateparam.sql

if ($argc < 2) {
	echo "Usage: templateparam.php <wikiname>\n";
	exit;
}

$wikiname = $argv[1];

$tmpl_count = 0;
$param_count = 0;
$pageid = -1;

$ohndl = fopen('templateparam.sql', 'w');

$text = <<<EOT
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `$wikiname`
--

DROP TABLE IF EXISTS `$wikiname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `$wikiname` (
  `page_id` int(10) unsigned NOT NULL,
  `template_name` varbinary(255) NOT NULL,
  `param_name` varbinary(255) NOT NULL,
  `param_value` varbinary(1024) NOT NULL,
  KEY `template_name` (`template_name`)
) ENGINE=Aria DEFAULT CHARSET=binary PAGE_CHECKSUM=1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `$wikiname`
--

LOCK TABLES `$wikiname` WRITE;
/*!40000 ALTER TABLE `$wikiname` DISABLE KEYS */;

EOT;

fwrite($ohndl, $text);

$ihndl = fopen('templateparam.txt', 'r');

while (! feof($ihndl)) {
	$buffer = fgets($ihndl);
	if (empty($buffer)) continue;

	if (preg_match('!^/mediawiki/page/id=(\d+)!', $buffer, $matches)) {
		if ($pageid >= 0) processPage($pageid, $pagetext);
		$pagetext = '';
		$pageid = (int)$matches[1];
	} elseif (preg_match('!^/mediawiki/page/revision/text=!', $buffer)) {
		$pagetext .= substr($buffer, 30);
	}
}

fclose($ihndl);

processPage($pagetext, $ohndl);

$text = <<<EOT

/*!40000 ALTER TABLE `$wikiname` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
EOT;

fwrite($ohndl, $text);

fclose($ohndl);

echo "Template count = $tmpl_count\n";
echo "Parameter count = $param_count\n";

/**
 * Process a pages templates.
 *
 * @param int $pageid
 * @param string $pagetext
 */
function processPage($pageid, $pagetext)
{
	global $tmpl_count, $param_count, $ohndl, $wikiname;

	$templates = getTemplates($pagetext);
	if (empty($templates)) return;

	$values = array();

	foreach ($templates as $template) {
		if (empty($template['params'])) continue;
		++$tmpl_count;
		$tmplname = sqlquote($template['name']);

		foreach ($template['params'] as $key => $value) {
			++$param_count;
			$key = sqlquote($key);
			if (strlen($value) > 1024) $value = substr($value, 0, 1024);
			$value = sqlquote($value);
			$values[] = "($pageid,'$tmplname','$key','$value')";
		}
	}

	if (empty($values)) return;

	$values = implode(',', $values);

	fwrite($ohndl, "INSERT INTO `$wikiname` VALUES $values;\n");
}

function sqlquote($value)
{
	$value = str_replace('\\', '\\\\', $value);
	$value = str_replace("'", "\\'", $value);
	return $value;
}

/**
 * Get template names and parameters in a string.
 *
 * @param string $origdata
 * @return array Templates array('name' => string, 'params' = array('name' => 'value')
 */
function getTemplates($origdata)
{
	static $regexs = array(
			'passed_param' => '!\{\{\{(?P<content>[^{}]*?\}\}\})!', // Highest priority
			'html' => '!<\s*(?P<content>(?P<tag>[\w]+)[^>]*>[^<]*?<\s*/\s*(?P=tag)\s*>)!',
			'template' => '!\{\{\s*(?P<content>(?P<name>[^{}\|]+?)(?:\|(?P<params>[^{}]+?))?\}\})!',
			'table' => '!\{\|(?P<content>[^{]*?\|\})!',
			'link' => '!\[\[(?P<content>[^\[\]]*?\]\])!'
	);

	static $MAX_ITERATIONS = 100000;

	$itercnt = 0;
	$match_found = true;
	$markers = array();
	$templates = array();
	$data = preg_replace('/<!--.*?-->/us', '', $origdata); // Strip comments

	while ($match_found) {
		if (++$itercnt > $MAX_ITERATIONS) {
			//Logger::log("Max iterations reached data=$origdata");
			return array();
		}
		$match_found = false;

		foreach ($regexs as $type => $regex) {
			$match_cnt = preg_match_all($regex, $data, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
			$offset_adjust = 0;

			if ($match_cnt) {
				$match_found = true;

				foreach ($matches as $match) {
					// See if there are any containers inside
					$content = $match['content'][0];

					foreach ($regexs as $regex2) {
						if (preg_match($regex2, $content)) {
							//								echo "$regex2\n";
							//								echo "$content\n";
							continue 2;
						}
					}

					// Replace the match with a marker
					$marker_id = "\v" . count($markers) . "\f";
					$content = $match[0][0];
					$content_len = strlen($content);
					$offset = $match[0][1] - $offset_adjust;
					$offset_adjust += $content_len - strlen($marker_id);

					$data = substr_replace($data, $marker_id, $offset, $content_len);

					if ($type == 'template') $templates[] = $content;

					// Replace any markers in the content
					preg_match_all("!\v\\d+\f!", $content, $marker_matches);
					foreach ($marker_matches[0] as $marker_match) {
						$content = str_replace($marker_match, $markers[$marker_match], $content);
					}

					$markers[$marker_id] = $content;
				}
			}
		}
	}

	$results = array();

	// Parse the template names and parameters
	foreach ($templates as $template) {
		preg_match($regexs['template'], $template, $matches);
		$tmpl_name = $matches['name'];

		// Replace any markers in the name
		preg_match_all("!\v\\d+\f!", $tmpl_name, $marker_matches);
		foreach ($marker_matches[0] as $marker_match) {
			$tmpl_name = str_replace($marker_match, $markers[$marker_match], $tmpl_name);
		}

		$tmpl_name = ucfirst(trim(str_replace('_', ' ', $tmpl_name)));
		if (strpos($tmpl_name, 'Template:') === 0) {
			$tmpl_name = ucfirst(ltrim(substr($tmpl_name, 9)));
		}

		$tmpl_params = array();
		if (isset($matches['params'])) {
			$numbered_param = 1;
			$params = explode('|', $matches['params']);

			foreach ($params as $param) {
				if (strpos($param, '=') !== false) {
					list($param_name, $param_value) = explode('=', $param, 2);

					// Replace any markers in the name
					preg_match_all("!\v\\d+\f!", $param_name, $marker_matches);
					foreach ($marker_matches[0] as $marker_match) {
						$param_name = str_replace($marker_match, $markers[$marker_match], $param_name);
					}
				} else {
					$param_name = "$numbered_param";
					$param_value = $param;
					++$numbered_param;
				}

				// Replace any markers in the content
				preg_match_all("!\v\\d+\f!", $param_value, $marker_matches);
				foreach ($marker_matches[0] as $marker_match) {
					$param_value = str_replace($marker_match, $markers[$marker_match], $param_value);
				}

				$param_name = trim($param_name);
				$tmpl_params[$param_name] = trim($param_value);
			}
		}

		$results[] = array('name' => $tmpl_name, 'params' => $tmpl_params);
	}

	return $results;
}
