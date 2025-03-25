<?php
/**
 Copyright 2025 Myers Enterprises II

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

namespace com_brucemyers\DumpScannerBot\Scanners;


class WDAmbiguousLabels extends ScannerBase
{
    const PROP_SUBCLASSOF = 'P279';
    const PROP_INSTANCEOF = 'P31';
    var $htmldir;
    var $outputdir;
    
    
    public function init($params)
    {
        $this->htmldir = $params['htmldir'];
        $this->outputdir = $params['outputdir'];
        
        return true;
    }
    
    public function commenceScan()
    {
        $hndl = fopen('php://stdin', 'r');
        $this->dumpLabels($hndl);
    }
    
    /**
     * 
     * @param resource $hndl closed at eof
     */
    public function dumpLabels($hndl)
    {
        $count = 0;
        $langswithdesc = [];
        $langswithoutdesc = [];
        
        while (! feof($hndl)) {
            if (++$count % 1000000 == 0) echo "Processed $count\n";
            $buffer = fgets($hndl);
            if (empty($buffer)) continue;
            
            $data = json_decode($buffer, true);
            if (! isset($data['id'])) continue;
            $qid = $data['id'];
            if (empty($qid)) continue;
            if ($qid[0] != 'Q') continue;
            $qid = (int)substr($qid, 1);
            
            $has_instanceof = false;
            $has_subclassof = false;
            $has_sitelink = false;
            
            if (isset($data['claims'])) {
                if (isset($data['claims'][$this->PROP_INSTANCEOF])) $has_instanceof = true;
                if (isset($data['claims'][$this->PROP_SUBCLASSOF])) $has_subclassof = true;
            }
            
            if (isset($data['sitelinks'])) {
                if (! empty($data['sitelinks']))$has_sitelink = true;
            }
            
            if ($has_instanceof == false && $has_subclassof == false && $has_sitelink == false) continue;
            
            $instsubsite = ($has_instanceof ? '1':'0') . ($has_subclassof ? '1':'0') . ($has_sitelink ? '1':'0');
            
            // Get labels/descriptions
                        
            $labelswithdescriptions = array_intersect_key($data['labels'], $data['descriptions']);
            $labelswithoutdescriptions = array_diff_key($data['labels'], $data['descriptions']);
            
            foreach ($labelswithdescriptions as $lang => $data) {
                if (strpos($lang, '-') || $lang == 'mul') continue;
                if (! isset($langswithdesc[$lang])) $langswithdesc[$lang] = [];
                $label = $data['value'];
                $langswithdesc[$lang][$label] = $qid;
            }
            
            foreach ($labelswithoutdescriptions as $lang => $data) {
                if (strpos($lang, '-') || $lang == 'mul') continue;
                if (! isset($langswithoutdesc[$lang])) $langswithoutdesc[$lang] = [];
                $label = $data['value'];
                $langswithoutdesc[$lang][] = [$label, $instsubsite, $qid];
            }
            
            // Dump batch of 1000
            
            foreach ($langswithdesc as $lang => &$labels) {
                if (count($labels) > 1000) {
                    $filepath = $this->outputdir . $lang . 'd';
                    $hndl = fopen($filepath);
                    
                    foreach ($labels as $label => $qid) {
                        fwrite($hndl, "$label\t$qid\n");
                    }
                    
                    fclose($hndl);
                    $labels = [];
                }
            }
            
            unset($labels);
            
            foreach ($labelswithoutdescriptions as $lang => &$labels) {
                if (count($labels) > 1000) {
                    $filepath = $this->outputdir . $lang . 'n';
                    $hndl = fopen($filepath);
                    
                    foreach ($labels as $data) {
                        fwrite($hndl, "{$data[0]}\t{$data[1]}\t{$data[2]}\n");
                    }
                    
                    fclose($hndl);
                    $labels = [];
                }
            }
            
            unset($labels);
        }
            
        fclose($hndl);
        
        // Dump remainder
        
        foreach ($langswithdesc as $lang => $labels) {
            $filepath = $this->outputdir . $lang . 'd';
            $hndl = fopen($filepath);
            
            foreach ($labels as $label => $qid) {
                fwrite($hndl, "$label\t$qid\n");
            }
            
            fclose($hndl);
        }
        
        foreach ($labelswithoutdescriptions as $lang => $labels) {
            $filepath = $this->outputdir . $lang . 'n';
            $hndl = fopen($filepath);
            
            foreach ($labels as $data) {
                fwrite($hndl, "{$data[0]}\t{$data[1]}\t{$data[2]}\n");
            }
            
            fclose($hndl);
        }
        
    }
    
    public function sortLabels()
    {
        
    }
    
    public function joinLabels()
    {
        
    }
}