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

use com_brucemyers\Util\FileCache;
use com_brucemyers\MediaWiki\AllPagesLister;
use com_brucemyers\MediaWiki\WikidataSPARQL;
use com_brucemyers\MediaWiki\WikidataWiki;
use com_brucemyers\Util\WikitableParser;
use com_brucemyers\Util\Config;
use com_brucemyers\DatabaseReportBot\DatabaseReportBot;
use com_brucemyers\ShEx\ShExDoc\ShExDocLexer;
use com_brucemyers\ShEx\ShExDoc\ShExDocParser;
use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use com_brucemyers\ShEx\ShExDoc\ShExDocDataCollectorListener;
use com_brucemyers\ShEx\ShExDoc\ShExDocErrorListener;


class WikidataEntitySchemaDirectory
{
    public function main($language)
    {
        FileCache::purgeExpired();
        $wdwiki = new WikidataWiki();
        
        // Get the schema list
        $schemas = [];
        $deleted = [
            'E363' => true
        ];
        
        $lister = new AllPagesLister($wdwiki, '640');
        
        while (($pages = $lister->getNextBatch()) !== false) {
            foreach ($pages as $page) {
                $id = substr($page['title'], 13);
                if (isset($deleted[$id])) continue;
                $schemas[$id] = [];
            }
        }
        
        // Get the configuration
        $config = $wdwiki->getPage('Wikidata:Database reports/EntitySchema directory/Configuration');
        
        // Get the see alsos
        $seealsos = $this->_calcSeeAlso($config);
        
        $configtable = WikitableParser::getTables($config)[0];
        
        foreach ($configtable['rows'] as $row) {
            preg_match('!E\d+!', $row[0], $matches);
            $id = $matches[0];
            $classprop = $row[1];
            $cats = $row[2];
            $status = $row[3];
            $lang = $row[4];
            
            if (isset($schemas[$id])) {
                $schemas[$id] = ['id' => $id, 'classprop' => $classprop, 'cats' => $cats, 'status' => $status, 'lang' => $lang, 'imports' => [], 'importedby' => [], 
                    'classes' => []
                ];
            }
        }
        
        // Retrieve the schema data
        $ids = [];
        foreach ($schemas as $id => $attribs) {
            $ids[] = 'EntitySchema:' . $id;
        }
        
        $schemadata = $wdwiki->getItemsWithCache($ids);
        
        foreach ($schemadata as $schemadatum) {
            $id = $schemadatum->getId();
            $schemas[$id]['data'] = $schemadatum;
            
            if (empty(trim($schemadatum->getSchemaText()))) $schemas[$id]['cats'] = 'Empty schema';
        }
        
        // Retrieve the class list (P12861)
        $query = "SELECT%20(STRAFTER(STR(%3Fitem)%2C%20'entity%2F')%20AS%20%3Fitemid)%20%3FitemLabel%20(STRAFTER(STR(%3Fvalue)%2C%20'E')%20as%20%3Fschema)%0A{%0A%20%20%3Fitem%20wdt%3AP12861%20%3Fvalue%20.%0A%20%20SERVICE%20wikibase%3Alabel%20{%20bd%3AserviceParam%20wikibase%3Alanguage%20\"$language%2Cen\"%20%20}%0A}%0A";
        
        $sparql = new WikidataSPARQL();
        
        $rows = $sparql->query($query);
        
        $props = [];
        
        foreach ($rows as $row) {
            $id = (int)$row['schema']['value'];
            $itemid = $row['itemid']['value'];
            if ($itemid[0] == 'P') $itemid = 'Property:' . $itemid;
            $itemlabel = $row['itemLabel']['value'];
            
            $schemas["E$id"]['classes'][] = ['id' => $itemid, 'label' => $itemlabel];
        }
        
        // Retrieve the labels for items and properties
        $labelids = [];
        $skip_validate = ['E67','E80','E81','E133','E263','E342'];
        
        foreach ($schemas as $id => $schema) {
            if (! isset($schema['classprop'])) continue; // New schema
            
            foreach ([$schema['classprop'], $schema['cats'], $schema['status']] as $attrib) {
                preg_match_all('!(?:Q\d+|P\d+)!', $attrib, $matches);
                
                foreach ($matches[0] as $match) {
                    if ($match[0] == 'P') $labelids[] = "Property:$match";
                    else $labelids[] = $match;
                }
            }
            
            // Calc imports
            preg_match_all('!IMPORT\s*<\s*https://www.wikidata.org/wiki/Special:EntitySchemaText/(E\d+)\s*>!', $schema['data']->getSchemaText(), $matches);
            
            foreach ($matches[1] as $match) {
                $schemas[$id]['imports'][] = $match;
                if (isset($schemas[$match])) $schemas[$match]['importedby'][] = $id;
            }
            
            // Validate the schema
            
            $schematext = $schemas[$id]['data']->getSchemaText();
            
            if (! empty(trim($schematext)) && ! in_array($id, $skip_validate)) {
                echo "validating schema $id\n";
                $input = InputStream::fromString($schematext);
                $lexer = new ShExDocLexer($input);
                $errorListener = new ShExDocErrorListener();
                $lexer->addErrorListener($errorListener);
                $tokens = new CommonTokenStream($lexer);
                
                $parser = new ShExDocParser($tokens);
                $parser->addErrorListener($errorListener);
                $dataCollector = new ShExDocDataCollectorListener();
                $parser->addParseListener($dataCollector);
                $parser->shExDoc();
                
                // Check for parsing errors
                $errors = $errorListener->getErrors();
                
                // Check for missing prefix
                $prefixes = $dataCollector->getPrefixes();
                $names = $dataCollector->getPrefixedNames();
                
                foreach ($names as $name) {
                    if (($colonpos = strpos($name['name'], ':')) !== false) {
                        $prefix = substr($name['name'], 0, $colonpos + 1);
                        
                        if (! isset($prefixes[$prefix])) {
                            $errors[] = ['line' => $name['line'], 'charpos' => $name['charpos'], 'msg' => "Prefix '$prefix' is not defined"];
                        }
                    }
                }
                
                // Find the earliest error
                if (! empty($errors)) {
                    // Sort by line number and column
                    usort($errors, function($a, $b) {
                        if ($a['line'] < $b['line']) return -1;
                        if ($a['line'] > $b['line']) return 1;
                        if ($a['charpos'] < $b['charpos']) return -1;
                        if ($a['charpos'] > $b['charpos']) return 1;
                        return 0;
                    });
                        
                        $schemas[$id]['error'] = $errors[0];
                        echo sprintf("line %d:%d %s\n", $errors[0]['line'], $errors[0]['charpos'], $errors[0]['msg']);
                }
            }
        }
        
        $templabeldata = $wdwiki->getItemsWithCache($labelids);
        $labeldata = [];
        
        foreach ($templabeldata as $ld) {
            $id = $ld->getId();
            $labeldata[$id] = $ld;
        }
        
        // Calc categories
        $categories = [];
        
        foreach ($schemas as $id => &$schema) {
            $schema['label'] = $schema['data']->getLabelDescription('label', $language);
            if (! isset($schema['classprop'])) continue; // New schema
            
            $cats = explode(',', $schema['cats']);
            $cats = array_map('trim', $cats);
            
            foreach ($cats as $cat) {
                $label = $cat;
                if (isset($labeldata[$cat])) {
                    $label = $labeldata[$cat]->getLabelDescription('label', $language);
                }
                
                if (! isset($categories[$label])) $categories[$label] = ['qid' => $cat, 'schemas' => []];
                $categories[$label]['schemas'][] = $schema;
            }
        }
        
        unset($schema);
        
        // Calc dependency trees
        $trees = $this->_calcDependencyTrees($schemas);
        
        // Write the html
        
        $path = Config::get(DatabaseReportBot::HTMLDIR) . 'drb' . DIRECTORY_SEPARATOR . 'WikidataEntitySchemaDirectory.html';
        $hndl = fopen($path, 'wb');
        
        // Header
        
        fwrite($hndl, "<!DOCTYPE html>
			<html><head>
			<meta http-equiv='Content-type' content='text/html;charset=UTF-8' />
			<title>Wikidata EntitySchema directory</title>
			<link rel='stylesheet' type='text/css' href='../css/cwb.css' />
			</head><body>
			<div style='display: table; margin: 0 auto;'>
			<h1>Wikidata EntitySchema Directory</h1>
			");
        
        // Body
        date_default_timezone_set('UTC');
        
        $current_date = date('Y-m-d H:i');
        
        $wikitext = "This is a programmatically generated directory of EntitySchemas. Any changes made to this page will be lost during the next update. To configure how this page is generated see the [[Wikidata:Database reports/EntitySchema directory/Configuration|Configuration]].<br />\n";
        $wikitext .= "Updated: <onlyinclude>$current_date (UTC)</onlyinclude>\n";
        
        // Sort the categories
        uksort($categories, function ($a, $b) { return strcasecmp($a, $b); });
        
        foreach ($categories as $catname => $catdata) {
            $catqid = $catdata['qid'];
            
            usort($catdata['schemas'], function ($a, $b) {
                $ret = strcasecmp($a['label'], $b['label']);
                if ($ret != 0) return $ret;
                return strcmp($a['id'], $b['id']);
            });
                
                $wikitext .= "==$catname==\n";
                
                if (! empty($seealsos[$catqid])) {
                    $wikitext .= "See also:\n";
                    
                    foreach ($seealsos[$catqid] as $seealso) {
                        $wikitext .= "*$seealso\n";
                    }
                }
                
                $wikitext .= "{| class=\"wikitable sortable\"\n|-\n! {{I18n|label}}\n! {{I18n|description}}\n! {{I18n|alias}}\n! {{I18n|class}}/{{I18n|property}}\n! {{P|12861}}\n! {{I18n|dependencies}}\n";
                
                foreach ($catdata['schemas'] as $schema) {
                    $id = $schema['data']->getId();
                    $description = $schema['data']->getLabelDescription('description', $language);
                    $aliases = $schema['data']->getAliases($language);
                    if (count($aliases) == 0) $aliases = '';
                    else $aliases = implode(' &vert; ', $aliases);
                    
                    $lang = trim($schema['lang']);
                    if (! empty($lang)) $lang = "<i>language:&nbsp;$lang</i>";
                    
                    // Format class/property, status
                    $cpcs = ['classprop' => '', 'cats' => '', 'status' => ''];
                    
                    foreach (['classprop' => $schema['classprop'], 'status' => $schema['status']] as $type => $attrib) {
                        $attrib = explode(',', $attrib);
                        $attrib = array_map('trim', $attrib);
                        $attrib = implode(', ', $attrib);
                        
                        preg_match_all('!(?:Q\d+|P\d+)!', $attrib, $matches);
                        
                        foreach ($matches[0] as $match) {
                            if ($match[0] == 'P') $labelid = "Property:$match";
                            else $labelid = $match;
                            
                            if (isset($labeldata[$match])) {
                                $label = $labeldata[$match]->getLabelDescription('label', $language);
                                
                                if (! empty($label)) {
                                    if ($type == 'status') $attrib = str_replace($match, "<i>$label</i>", $attrib);
                                    else $attrib = str_replace($match, "[[$labelid|$label]]", $attrib);
                                }
                            }
                        }
                        
                        $cpcs[$type] = $attrib;
                    }
                    
                    // Calc dependencies
                    $imports = [];
                    $importedby = [];
                    
                    foreach ($schema['imports'] as $import) {
                        if (isset($schemas[$import])) $imports[] = "[[EntitySchema:$import|" . $schemas[$import]['data']->getLabelDescription('label', $language) . "]] ($import)";
                        else $imports[] = "<b>Missing schema ($import)<b>";
                    }
                    
                    foreach ($schema['importedby'] as $import) {
                        if (isset($schemas[$import])) $importedby[] = "[[EntitySchema:$import|" .$schemas[$import]['data']->getLabelDescription('label', $language) . "]] ($import)";
                        else $importedby[] = "<b>Missing schema ($import)<b>";
                    }
                    
                    $classes = [];
                    
                    foreach ($schema['classes'] as $class) {
                        $classes[] = "[[{$class['id']}|{$class['label']}]]";
                    }
                    
                    $classes = implode(', ', $classes);
                    
                    $depenencies = [];
                    
                    if (! empty($imports)) {
                        $depenencies[] = 'Imports: ' . implode(', ', $imports);
                    }
                    
                    if (! empty($importedby)) {
                        $depenencies[] = 'Imported by: ' . implode(', ', $importedby);
                    }
                    
                    $depenencies = implode('<br />', $depenencies);
                    
                    $wikitext .= "|-\n|[[EntitySchema:$id|{$schema['label']}]] ($id) {$cpcs['status']} $lang ||$description ||$aliases ||{$cpcs['classprop']} ||$classes ||$depenencies\n";
                    
                    if (isset($schema['error'])) {
                        $error = $schema['error'];
                        $errmsg = sprintf("line %d column %d %s", $error['line'], $error['charpos'], $error['msg']);
                        $wikitext .= "|-\n|colspan='5'| &nbsp;&nbsp;&nbsp;&rdsh; <b>Schema validation error</b>: $errmsg\n";
                    }
                }
                
                $wikitext .= "|}\n";
        }
        
        // Write dependency trees
        $wikitext .= "==Dependency trees==\n";
        $wikitext .= "An entity 'depends on'/imports the entities to its left.\n";
        
        uasort($trees, function ($a, $b) {
            $ret = strcasecmp($a['label'], $b['label']);
            if ($ret != 0) return $ret;
            return strcmp($a['id'], $b['id']);
        });
            
        foreach ($trees as $treeid => $tree) {
            $treedeps = $tree['deps'];
            $wikitext .= "<table style='border: 1px solid black; border-collapse: collapse;'>\n";
            $schema = $schemas[$treeid];
            $wikitext .= "<tr><td style='border: 1px solid black;'>[[EntitySchema:$treeid|{$schema['label']}]] ($treeid)</td><td style='border: 1px solid black;'>\n";
            $wikitext .= $this->_displayTree($treedeps, $schemas);
            $wikitext .= "</td></tr>\n";
            $wikitext .= "</table><br />\n";
        }
        
        $wikitext .= "\n[[Category:Database reports]]\n[[Category:WikiProject Schemas]]";
        
        fwrite($hndl, '<form><textarea rows="40" cols="100" name="wikitable" id="wikitable">' . htmlspecialchars($wikitext) .
            '</textarea>');
        
        $wikitext = '';
        
        foreach ($schemas as $id => $schema) {
            if (! isset($schema['classprop'])) $wikitext .= "|-\n| [[EntitySchema:$id|$id]] || || || ||\n";
        }
        
        fwrite($hndl, '<textarea rows="40" cols="100" name="newwikitable" id="newwikitable">' . htmlspecialchars($wikitext) .
            '</textarea></form>');
        
        // Footer
        
        fwrite($hndl, '<br />Schema count: ' . count($schemas));
        fwrite($hndl, '<br />Language: ' . $language);
        fwrite($hndl, "</div><br /><div style='display: table; margin: 0 auto;'>Author: <a href='https://en.wikipedia.org/wiki/User:Bamyers99'>Bamyers99</a></div></body></html>");
        fclose($hndl);
    }
    
    /**
     * Calculate Wikidata EntitySchema category see alsos
     *
     * @param string $config
     * @return array see alsos, key = category qid
     */
    function _calcSeeAlso($config)
    {
        $seealsos = [];
        $lines = preg_split('!\R!u', $config);
        $maxlines = count($lines);
        
        // Find the Categories section
        $curline = 0;
        while ($curline < $maxlines && $lines[$curline] != '==Categories==') ++$curline;
        
        if ($curline == $maxlines) {
            echo "==Categories== not found\n";
            exit;
        }
        
        ++$curline;
        $curcat = '';
        
        for (; $curline < $maxlines && (strlen($lines[$curline]) == 0 || $lines[$curline][0] != '='); ++$curline) {
            if (strlen($lines[$curline]) < 2) continue;
            $first2 = substr($lines[$curline], 0, 2);
            if ($first2[0] != '*') continue;
            
            if ($first2 == '**') {
                if (empty($curcat)) {
                    echo "See also category not found for ({$lines[$curline]})\n";
                    exit;
                }
                
                if (! isset($seealsos[$curcat])) $seealsos[$curcat] = [];
                $seealsos[$curcat][] = trim(substr($lines[$curline], 2));
                
            } else {
                if (preg_match('!\{\{Q\|(\d+)\}\}!', $lines[$curline], $matches)) {
                    $curcat = 'Q' . $matches[1];
                }
            }
        }
        
        return $seealsos;
    }
    
    function _calcDependencyTrees($schemas)
    {
        $dependees = [];
        $dependants = [];
        
        foreach ($schemas as $id => $schema) {
            if (! isset($schema['importedby'])) continue;
            
            foreach ($schema['importedby'] as $impbyid) {
                if (! isset($dependees[$id])) $dependees[$id] = [];
                $dependees[$id][] = $impbyid;
                
                if (! isset($dependants[$impbyid])) $dependants[$impbyid] = [];
                $dependants[$impbyid][] = $id;
            }
        }
        
        $roots = [];
        
        foreach (array_keys($dependees) as $id) {
            if (! isset($dependants[$id]) && ! in_array($id, $roots)) $roots[] = $id;
        }
        
        $trees = [];
        
        foreach ($roots as $root) {
            echo "calcing dependees schema $root\n";
            $label = $schemas[$root]['label'];
            $id = $schemas[$root]['id'];
            $trees[$root] = ['id' => $id, 'label' => $label, 'deps' => $this->_getDependants($dependees, $root, "#$root#")];
        }
        
        return $trees;
    }
    
    function _getDependants($dependees, $root, $parenttree)
    {
        $deps = [];
        
        if (! isset($dependees[$root])) return $deps;
        
        foreach ($dependees[$root] as $depid) {
            // Check for circular reference
            if (strpos($parenttree, "#$depid#") !== false) {
                $deps[$depid] = "Circular reference";
                continue;
            }
            
            $deps[$depid] = $this->_getDependants($dependees, $depid, $parenttree . "#$depid#");
        }
        
        return $deps;
    }
    
    function _displayTree($tree, $schemas)
    {
        $wikitext = '';
        
        if (empty($tree)) return $wikitext;
        
        $wikitext .= "<table style='border-collapse: collapse;'>\n";
        
        foreach ($tree as $id => $subtree) {
            $schema = $schemas[$id];
            $wikitext .= "<tr><td style='border: 1px solid black;'>[[EntitySchema:$id|{$schema['label']}]] ($id)";
            
            if (! is_array($subtree)) $wikitext .= " $subtree</td>"; // Error message
            else {
                if (! empty($subtree)) $wikitext .= "</td><td style='border: 1px solid black;'>" . $this->_displayTree($subtree, $schemas) . "</td>";
            }
            
            $wikitext .= "</tr>\n";
        }
        
        $wikitext .= "</table>\n";
        
        return $wikitext;
    }
 }