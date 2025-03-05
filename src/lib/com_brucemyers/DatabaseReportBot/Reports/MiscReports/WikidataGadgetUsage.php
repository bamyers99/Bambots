<?php
/**
 Copyright 2021 Myers Enterprises II

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

namespace com_brucemyers\DatabaseReportBot\Reports\MiscReports;

use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\MediaWiki\WikidataWiki;
use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Curl;
use com_brucemyers\Util\WikitableParser;
use com_brucemyers\Util\CommonRegex;
use com_brucemyers\Util\ShellExec;
use com_brucemyers\Util\TemplateParamParser;
use com_brucemyers\Util\Config;
use com_brucemyers\Util\Logger;
use PDO;

class WikidataGadgetUsage
{
    /**
     * Wikidata gadget usage
     *
     * @param string $language
     * @param PDO $dbh_wikidata
     * @param bool $testing
     */
    public function main($language, PDO $dbh_wikidata, $testing, $user, $pass)
    {
        
        $sections = [
            '=== wikidata ===' => ['type' => 'wikidata'],
            '=== general ===' => ['type' => 'general'],
            '=== admin-gadgets ===' => ['type' => 'admin'],
            '=== hidden ===' => ['type' => 'hidden']
        ];
        
        $wdwiki = new WikidataWiki();
        
        // Get the approved gadget list
        
        $text = $wdwiki->getpage('MediaWiki:Gadgets-definition');
        
        $gadgets = [];
        $pagenames = [];
        
        $lines = preg_split('/\\r?\\n/u', $text);
        $type = 'skip';
        
        foreach ($lines as $line) {
            if (isset($sections[$line])) {
                $type = $sections[$line]['type'];
                continue;
            }
            
            if ($type == 'skip') continue;
            
            if (preg_match('!\*\s*([^\[|]+?)(?:\[|\|)!', $line, $matches)) {
                $gadget = trim($matches[1]);
                $gadgets[$gadget] = ['name' => $gadget,'type' => $type, 'location' => 'preferences', 'host' => 'wikidata'];
                $pagenames[] = "MediaWiki:Gadget-$gadget/$language";
                $pagenames[] = "MediaWiki:Gadget-$gadget"; // fallback
            }
        }
        
        // Get the approved gadget descriptions
        $descriptions = $wdwiki->getPagesWithCache($pagenames, ! $testing); // refetch if not testing
        
        foreach ($descriptions as $page_name => $description) {
            preg_match("!^MediaWiki:Gadget-([^/]+?)(?:/$language)?$!u", $page_name, $matches);
            $gadget = $matches[1];
            $lang_found = false;
            if (substr($page_name, -(strlen($language) + 1)) == "/$language") $lang_found = true;
            
            if ((! empty($gadgets[$gadget]['description']) && ! $lang_found) || $description === false) continue;
            
            $description = preg_replace('!<noinclude>.*?</noinclude>!us', '', $description);
            $gadgets[$gadget]['description'] = $description;
        }
        
        // Get the approved gadget totals
        
        $text = Curl::getUrlContents('https://www.wikidata.org/wiki/Special:GadgetUsage');
        
        // <tr><td><a href="/wiki/Special:Gadgets#gadget-AuthorityControl" title="Special:Gadgets">AuthorityControl</a></td><td data-sort-value="Infinity">Default</td><td data-sort-value="Infinity">Default</td></tr>
        // <tr><td><a href="/wiki/Special:Gadgets#gadget-labelLister" title="Special:Gadgets">labelLister</a></td><td>5,236</td><td>1,276</td></tr><tr>
        
        preg_match_all('!<tr><td><a[^>]+?>([^<]+?)</a></td><td[^>]*?>([^<]+?)</td><td[^>]*?>([^<]+?)</td></tr>!u', $text, $matches, PREG_SET_ORDER);
        
        if (count($matches) == 0) {
            Logger::log('Special:GadgetUsage not parsed');
            exit;
        }
        
        foreach ($matches as $match) {
            $gadget = str_replace('_', ' ', $match[1]);
            $numusers = $match[2];
            $activeusers = $match[3];
            
            if ($numusers == 'Default') {
                unset($gadgets[$gadget]);
                continue;
            }
            
            if (! isset($gadgets[$gadget])) {
                echo "Special:GadgetUsage '$gadget' not found\n";
                continue;
            }
            
            $gadgets[$gadget]['numusers'] = str_replace(',', '', $numusers);
            $gadgets[$gadget]['activeusers'] = str_replace(',', '', $activeusers);
        }
        
        foreach ($gadgets as $gadget => $data) {
            if (! isset($data['numusers'])) unset($gadgets[$gadget]);
        }
        
        // Get the user gadgets
        
        $sql = "SELECT page_title FROM page WHERE page_namespace = 2 AND page_title REGEXP '/(common|vector|monobook|vector-2022)\.js$'";
        
        $sth = $dbh_wikidata->query($sql);
        $pagenames = [];
        
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            $pagenames[] = 'User:' . $row[0];
        }
        
        echo 'Processing common.js files = ' . count($pagenames) . "\n";
        
        $chunks = array_chunk($pagenames, 100);
        $script_names = [];
        
        foreach ($chunks as $chunk) {
            $scripts = $wdwiki->getPagesWithCache($chunk, ! $testing); // refetch if not testing
            
            foreach ($scripts as $pagename => $script) {
                preg_match('!^User:([^/]+)/!', $pagename, $matches);
                $username = $matches[1];
                $user_gadgets = [];
                $script = preg_replace(CommonRegex::COMMENT_REGEX, '', $script);
                $script = preg_replace('!/\*[\s\S]*?\*/!u', '', $script);
                $lines = preg_split('/\\r?\\n/u', $script);
                
                foreach ($lines as $line) {
                    // Only look at importScript() and mw.loader.load()
                    if (! preg_match('!(importScript|mw.loader.load)\s*\(\s*[\'"]([^\'"]+[\'"])!', $line, $matches)) continue; // final [\'"] for json check
                    $loader = $matches[1];
                    $loaderdata = $matches[2];
                    
                    if ($loader == 'mw.loader.load') $loaderdata = urldecode($loaderdata);
                    
                    if (! preg_match('!^\s*//!', $line) && preg_match('!User:([^/]+?/[^\.]+?)\.js[^o]!u', $loaderdata, $matches)) { // skip .json
                        $gadget = str_replace(' ', '_', $matches[1]);
                        $host = 'wikidata';
                        
                        if ($loader == 'mw.loader.load') {
                            if (strpos($loaderdata, 'meta.wikimedia.org') !== false) $host = 'meta';
                            elseif (strpos($loaderdata, 'en.wikipedia.org') !== false) $host = 'enwiki';
                            elseif (strpos($loaderdata, 'wikidata.org') !== false || strpos($loaderdata, '//') === false) $host = 'wikidata';
                            else {
                                echo "Other host = $loaderdata\n";
                                continue;
                            }
                        }
                        
                        if (strpos($gadget, '|') === false) $user_gadgets[$gadget] = $host; // removes dups
                    }
                }
                
                foreach ($user_gadgets as $gadget => $host) {
                    if (! isset($gadgets[$gadget])) {
                        $gadgets[$gadget] = ['name' => $gadget, 'type' => 'wikidata', 'location' => 'common.js', 'numusers' => 0,
                            'activeusers' => 0, 'description' => '', 'host' => $host, 'usernames' => []];
                        $script_names["User:$gadget.js"] = $host;
                    }
                    
                    if (! in_array($username, $gadgets[$gadget]['usernames'])) {
                        ++$gadgets[$gadget]['numusers'];
                        $gadgets[$gadget]['usernames'][] = $username;
                    }
                }
            }
        }
        
        // Get the metawiki global gadgets
        $dbh_metawiki = new PDO("mysql:host=metawiki.analytics.db.svc.eqiad.wmflabs;dbname=metawiki_p;charset=utf8mb4", $user, $pass);
        $dbh_metawiki->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $metawiki = new MediaWiki('https://meta.wikimedia.org/w/api.php');
        $enwiki = new MediaWiki('https://en.wikipedia.org/w/api.php');
        
        $sql = "SELECT page_title FROM page WHERE page_namespace = 2 AND page_title REGEXP '/global\.js$'";
        
        $sth = $dbh_metawiki->query($sql);
        $pagenames = [];
        
        while ($row = $sth->fetch(PDO::FETCH_NUM)) {
            $pagenames[] = 'User:' . $row[0];
        }
        
        echo 'Processing global.js files = ' . count($pagenames) . "\n";
        
        $chunks = array_chunk($pagenames, 100);
        
        foreach ($chunks as $chunk) {
            $scripts = $metawiki->getPagesWithCache($chunk, ! $testing); // refetch if not testing
            
            foreach ($scripts as $pagename => $script) {
                preg_match('!^User:([^/]+)/!', $pagename, $matches);
                $username = $matches[1];
                $user_gadgets = [];
                $script = preg_replace(CommonRegex::COMMENT_REGEX, '', $script);
                $script = preg_replace('!/\*[\s\S]*?\*/!u', '', $script);
                $lines = preg_split('/\\r?\\n/u', $script);
                
                foreach ($lines as $line) {
                    if (! preg_match('!(importScript|mw.loader.load)\s*\(\s*[\'"]([^\'"]+[\'"])!', $line, $matches)) continue; // final [\'"] for json check
                    $loader = $matches[1];
                    $loaderdata = $matches[2];
                    
                    if ($loader == 'mw.loader.load') $loaderdata = urldecode($loaderdata);
                    
                    if (! preg_match('!^\s*//!', $line) && preg_match('!(?:www\.wikidata\.org|meta\.wikimedia\.org|en\.wikipedia\.org).*?User:([^/]+?/[^\.]+?)\.js[^o]!u', $loaderdata, $matches)) { // skip .json
                        $host = 'wikidata';
                        
                        if ($loader == 'mw.loader.load') {
                            if (strpos($loaderdata, 'meta.wikimedia.org') !== false) $host = 'meta';
                            elseif (strpos($loaderdata, 'en.wikipedia.org') !== false) $host = 'enwiki';
                            elseif (strpos($loaderdata, 'wikidata.org') !== false) $host = 'wikidata';
                            else {
                                echo "Other host = $loaderdata\n";
                                continue;
                            }
                        }
                        
                        $gadget = str_replace(' ', '_', $matches[1]);
                        if (strpos($gadget, '|') === false) $user_gadgets[$gadget] = $host; // removes dups
                    }
                }
                
                foreach ($user_gadgets as $gadget => $host) {
                    if (! isset($gadgets[$gadget])) {
                        $gadgets[$gadget] = ['name' => $gadget, 'type' => 'wikidata', 'location' => 'common.js', 'numusers' => 0,
                            'activeusers' => 0, 'description' => '', 'host' => $host, 'usernames' => []];
                        $script_names["User:$gadget.js"] = $host;
                    }
                    
                    if (! in_array($username, $gadgets[$gadget]['usernames'])) {
                        ++$gadgets[$gadget]['numusers'];
                        $gadgets[$gadget]['usernames'][] = $username;
                    }
                }
            }
        }

        // Verify that the user gadgets still exist and run linter
        
        foreach (['wikidata', 'meta', 'enwiki'] as $checkhost) {
            $pages = [];
            foreach ($script_names as $gadget => $host) {
                if ($checkhost == $host) $pages[] = $gadget;
            }
        
            if ($checkhost == 'wikidata') {
                $gadget_scripts = $wdwiki->getPagesWithCache($pages, ! $testing); // refetch if not testing
            } elseif ($checkhost == 'meta') {
                $gadget_scripts = $metawiki->getPagesWithCache($pages, ! $testing); // refetch if not testing
            } else {
                $gadget_scripts = $enwiki->getPagesWithCache($pages, ! $testing); // refetch if not testing
            }
            
            foreach ($gadget_scripts as $script_name => $script) {
                preg_match('!User:([^/]+?/[^\.]+?)\.js!u', $script_name, $matches);
                $gadget = $matches[1];
                
                if ($script !== false && ! empty($script)) {
                    // 	            $linters = $this->_getUserGadgetLint($script_name);
                    
                    // 	            if (! empty($linters)) {
                    // 	            }
                    
                    continue;
                }
            
                unset($gadgets[$gadget]);
            }
        }
    
        // Retrieve the user gadget descriptions
        
        $this->_getUserGadgetDescriptons($wdwiki, $gadgets, $testing);
        
        // Get the configuration and add descriptions, suppress gadgets
        
        $config = $wdwiki->getPage('Wikidata:Database reports/Gadget usage statistics/Configuration');
        
        $configtable = WikitableParser::getTables($config)[0];
        
        foreach ($configtable['rows'] as $row) {
            preg_match('!User:([^/]+?/[^\.]+?)\.js[^o]!u', $row[0], $matches);
            $gadget = str_replace(' ', '_', $matches[1]);
            $description = $row[1];
            $status = $row[2];
            
            if (! isset($gadgets[$gadget])) {
                echo "Unknown configuration gadget = $gadget\n";
                continue;
            }
            
            if ($status == 'suppress') {
                unset($gadgets[$gadget]);
                continue;
            }
            
            if ($status == 'deprecated') $gadgets[$gadget]['deprecated'] = true;
            
            if (empty($gadgets[$gadget]['description'])) $gadgets[$gadget]['description'] = $description;
        }
        
        // Get the user gadget active user counts
        $this->_getUserGadgetActiveUsers($dbh_wikidata, $gadgets);
        
        // Get creation dates
        $this->_getUserGadgetCreation($wdwiki, $gadgets);
        
        // Sort by number of uses
        
        uasort($gadgets, function($a, $b) {
            $a_count = (int)$a['numusers'];
            $b_count = (int)$b['numusers'];
            
            if ($a_count < $b_count) return 1; // descending
            if ($a_count > $b_count) return -1;
            return strcasecmp($a['name'], $b['name']);
        });
        
        $this->_writePage('all', $gadgets, $language);
        $this->_writePage('wikidata', $gadgets, $language);
        $this->_writePage('meta', $gadgets, $language);
        $this->_writePage('enwiki', $gadgets, $language);
    }

    /**
     * Write host specific page.
     * 
     * @param string $host
     * @param array $gadgets
     * @param string $language
     */
    function _writePage($host, $gadgets, $language)
    {
        $asof_date = getdate();
        $asof_date = $asof_date['month'] . ' '. $asof_date['mday'] . ', ' . $asof_date['year'];
        $path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . "WikidataGadgetUsage-$host.html";
        $hndl = fopen($path, 'wb');
        
        // Header
        fwrite($hndl, "<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
			<title>Wikidata gadget usage</title>
			<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
			</head><body>
			<div style='display: table; margin: 0 auto;'>
			<h1>Wikidata gadget usage</h1>
			<h3>As of $asof_date</h3>
			");
        
        // Body
        
        date_default_timezone_set('UTC');
        $current_date = date('Y-m-d H:i');
        $gadget_count = ['wikidata' => 0, 'meta' => 0, 'enwiki' => 0];
        
        $wikitext = "This is a programmatically generated summary of gadget usage. Includes gadgets enabled in [[Special:Preferences#mw-prefsection-gadgets|preferences]] or [[Special:MyPage/common.js|common.js]]. Any changes made to this page will be lost during the next update.<br />\n";
        $wikitext .= "Updated: <onlyinclude>$current_date (UTC)</onlyinclude>\n";
        
        $wikitext .= "{{Start tab
| tab-1         = All
| link-1        = Wikidata:Database reports/Gadget usage statistics
| tab-2         = wikidata
| link-2        = Wikidata:Database reports/Gadget usage statistics/wikidata
| tab-3         = metawiki
| link-3        = Wikidata:Database reports/Gadget usage statistics/metawiki
| tab-4         = enwiki
| link-4        = Wikidata:Database reports/Gadget usage statistics/enwiki
| border        = 1px solid #808080
| off tab color = #f0f0ff
| on tab color  = 
| rounding      = 5em
| tab alignment = center
}}\n";
        
        if ($host == 'wikidata') {
            $wikitext .= "{| class=\"wikitable sortable\"\n|-\n! {{I18n|gadget}}\n! {{I18n|description}}\n! {{I18n|enabled in}}\n! {{I18n|created}}\n! {{I18n|number of users}}\n! {{I18n|active users}}\n";
        } else {
            $wikitext .= "{| class=\"wikitable sortable\"\n|-\n! {{I18n|gadget}}\n! {{I18n|description}}\n! {{I18n|enabled in}}\n! {{I18n|number of users}}\n! {{I18n|active users}}\n";
        }
        
        foreach ($gadgets as $gadget => $data) {
            if ($host != 'all' && $host != $data['host']) continue;
            
            $description = $data['description'];
            $location = $data['location'];
            
            $numusers = $data['numusers'];
            $numusers = number_format($numusers, 0);
            
            $activeusers = $data['activeusers'];
            if ($activeusers == 0) {
                $activeusers = '';
            } else {
                $activeusers = number_format($activeusers, 0);
            }
            
            $gadgetfield = $gadget;
            
            if ($location == 'common.js') {
                if (! isset($data['toolpage']) && $data['numusers'] < 5) continue;
                $metaqualifer = '';
                
                if ($host == 'all') {
                    if ($data['host'] == 'meta') $metaqualifer = ' (metawiki)';
                    elseif ($data['host'] == 'enwiki') $metaqualifer = ' (enwiki)';
                }
                
                if (isset($data['toolpage'])) {
                    $anchor = str_replace(' ', '_', $data['name']);
                    $gadgetfield = "[[{$data['toolpage']}#$anchor|{$data['name']}]]$metaqualifer";
                } else {
                    $display_gadget = str_replace('_', ' ', $gadget);
                    if (isset($data['deprecated'])) $display_gadget = '<span style="text-decoration: line-through #DB4325;">' . $display_gadget . '</span>';
                    $metaprefix = '';
                    if ($data['host'] == 'meta') $metaprefix = 'm:';
                    elseif ($data['host'] == 'enwiki') $metaprefix = 'w:en:';
                    $gadgetfield = "[[{$metaprefix}User:$gadget.js|$display_gadget]]$metaqualifer";
                }
            }
            
            if (empty($description)) $description = ' ';
            
            if ($host == 'wikidata') {
                $created = ' ';
                if (isset($data['created'])) $created = $data['created'];
                
                $wikitext .= "|-\n||$gadgetfield||$description\n||$location||$created|| style=\"text-align: right;\" |$numusers|| style=\"text-align: right;\" |$activeusers\n";
            } else {
                $wikitext .= "|-\n||$gadgetfield||$description\n||$location|| style=\"text-align: right;\" |$numusers|| style=\"text-align: right;\" |$activeusers\n";
            }
            
            ++$gadget_count[$data['host']];
        }
        
        $wikitext .= "|}\n\nNote: Uses [[Special:GadgetUsage]] to get preference enabled gadget totals.";
        $wikitext .= "\n\nNote: Includes uncatalogued gadgets (not listed on [[Wikidata:Tools]]) used by 5+ users.";
        if ($host == 'all') $wikitext .= "\n\n[[Category:Database reports]]\n[[Category:Wikidata statistics]]\n";
        
        fwrite($hndl, '<form><textarea rows="40" cols="100" name="wikitable" id="wikitable">' . htmlspecialchars($wikitext) .
            '</textarea></form>');
        
        // Footer
        fwrite($hndl, '<br />Gadget count: ' . ($gadget_count['wikidata'] + $gadget_count['meta'] + $gadget_count['enwiki']) .
            ' wikidata hosted: ' . $gadget_count['wikidata'] .
            ' metawiki hosted: ' . $gadget_count['meta'] .
            ' enwiki hosted: ' . $gadget_count['enwiki']);
        fwrite($hndl, '<br />Language: ' . $language);
        fwrite($hndl, "</div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
        fclose($hndl);
    }
    
    /**
     * Get user gadget descriptions
     *
     * @param $wdwiki
     * @param array $gadgets
     * @param bool $testing
     */
    function _getUserGadgetDescriptons($wdwiki, &$gadgets, $testing)
    {
        $tools_pages = ['Wikidata:Tools/Edit_items/en', 'Wikidata:Tools/Query_data/en', 'Wikidata:Tools/Enhance user interface/en', 'Wikidata:Tools/Visualize data/en',
            'Wikidata:List of properties/en', 'Wikidata:Tools/Lexicographical data/en', 'Wikidata:Tools/For programmers/en',
        ];
        
        $tools_pages = $wdwiki->getPagesWithCache($tools_pages, ! $testing); // refetch if not testing
        
        $tools = [];
        
        foreach ($tools_pages as $page_name => $page) {
            $templates = TemplateParamParser::getTemplates($page);
            
            foreach ($templates as $template) {
                if ($template['name'] != 'Tool2') continue;
                $params = $template['params'];
                
                if (empty($params['link']) || empty($params['features'])) continue;
                
                if (! preg_match('!User:([^/]+?/[^\.]+?)\.js!u', $params['link'], $matches)) continue;
                $gadget = str_replace(' ', '_', $matches[1]);
                
                $tools[$gadget] = ['name' => $params['name'], 'features' => $params['features'], 'pagename' => $page_name];
            }
        }
        
        foreach ($gadgets as $gadget => $data) {
            if ($data['location'] == 'common.js' && isset($tools[$gadget])) {
                $gadgets[$gadget]['name'] = $tools[$gadget]['name'];
                $gadgets[$gadget]['description'] = $tools[$gadget]['features'];
                $gadgets[$gadget]['toolpage'] = $tools[$gadget]['pagename'];
            }
        }
    }
    
    /**
     * Get user gadget linters
     *
     * @param $script
     * @return array
     */
    function _getUserGadgetLint($script_name)
    {
        $cache_dir = FileCache::getCacheDir();
        $cache_filepath = $cache_dir . '/' . FileCache::safeKey($script_name);
        
        $cmd = 'npx eslint --no-eslintrc --parser-options=ecmaVersion:11 -f json ' . $cache_filepath;
        
        $stdout = ShellExec::exec($cmd);
        
        $stdout = json_decode($stdout, true);
        
        if (! empty($stdout[0]['messages'])) {
            echo "$script_name\n";
            print_r($stdout[0]['messages']);
        }
        
        return $stdout[0]['messages'];
    }
    
    /**
     * Get user gadget active user counts
     *
     * @param PDO $dbh_wikidata
     * @param array $gadgets
     */
    function _getUserGadgetActiveUsers(PDO $dbh_wikidata, &$gadgets)
    {
        $usernames = [];
        
        foreach ($gadgets as $gadget) {
            if ($gadget['location'] == 'common.js') {
                foreach ($gadget['usernames'] as $username) {
                    $usernames[$username] = false;
                }
            }
        }
        
        $chunks = array_chunk(array_keys($usernames), 100);
        
        foreach ($chunks as $chunk) {
            $chunknames = [];
            
            foreach ($chunk as $username) {
                $chunknames[] = $dbh_wikidata->quote($username);
            }
            
            $sql = "SELECT actor_name FROM actor_recentchanges WHERE actor_name IN ( "  . implode(',', $chunknames) . ")";
            
            $sth = $dbh_wikidata->query($sql);
            
            while ($row = $sth->fetch(PDO::FETCH_NUM)) {
                $usernames[$row[0]] = true;
            }
        }
        
        foreach ($gadgets as $gadget => $data) {
            if ($data['location'] == 'common.js') {
                foreach ($data['usernames'] as $username) {
                    if ($usernames[$username] === true) {
                        ++$gadgets[$gadget]['activeusers'];
                    }
                }
            }
        }
    }
    
    /**
     * Get wikidata gadget creation date
     * 
     * @param $wdwiki
     * @param array $gadgets
     */
    function _getUserGadgetCreation($wdwiki, &$gadgets)
    {
        foreach ($gadgets as $gadget => $data) {
            if ($data['location'] != 'common.js' || $data['host'] != 'wikidata') continue;
            
            $params = [
                'titles' => "User:$gadget.js",
                'rvlimit' => 1,
                'rvdir' => 'newer'
            ];
            
            $ret = $wdwiki->getProp('revisions', $params);
            
            $ret = reset($ret['query']['pages']);
            
            $gadgets[$gadget]['created'] = substr($ret['revisions'][0]['timestamp'], 0, 10);
        }
    }
}
