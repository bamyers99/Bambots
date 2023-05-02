<?php
/**
 Copyright 2023 Myers Enterprises II

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

namespace com_brucemyers\TemplateParamBot;

use com_brucemyers\MediaWiki\MediaWiki;
use Exception;

class TemplateDataLister
{
    protected $mediawiki;
    protected $params;
    protected $pageids = [];
    protected $debug;

    /**
     * Constructor
     *
     * @param $mediawiki MediaWiki
     */
    public function __construct($mediawiki, $debug = false)
    {
        $this->mediawiki = $mediawiki;
        $this->params = [
        ];
        $this->debug = $debug;
        
        // Retrieve the templatedata page ids
        $params = [
            'pwppropname' => 'templatedata',
            'pwplimit' => 'max'
        ];
        $continue = ['continue' => ''];
        
        while ($continue !== false) {
            $pwpparams = array_merge($params, $continue);
            
            $ret = $mediawiki->getList('pageswithprop', $pwpparams);
            
            if (isset($ret['error'])) throw new Exception('TemplateDataLister failed ' . $ret['error']);
            if (isset($ret['continue'])) $continue = $ret['continue'];
            else $continue = false;
            
            foreach ($ret['query']['pageswithprop'] as $page) {
                $this->pageids[] = $page['pageid'];
            }
        }
        
        if ($debug) echo "Template count: " . count($this->pageids) . "\n";
    }

    /**
     * Get next batch of templatedatas
     *
     * @return mixed false: no more pages, array 
     */
    public function getNextBatch()
    {
        // Try a batch of 50
        $batchids = array_splice($this->pageids, 0, 50);
        
        if (empty($batchids)) return false;
        
        try {
            $batchdata = [];
            $pageids = implode('|', $batchids);
            $continue = ['continue' => ''];
            
            while ($continue !== false) {
                $temptdparams = array_merge($this->params, $continue);
                $temptdparams['pageids'] = $pageids;
                
                $ret = $this->mediawiki->getTemplateData($temptdparams);
                
                if (isset($ret['continue'])) $continue = $ret['continue'];
                else $continue = false;
                
                if (! empty($ret['pages'])) {
                    foreach ($ret['pages'] as $templid => $pagedata) {
                        $batchdata[$templid] = $pagedata;
                    }
                }
            }
            
            if ($this->debug) echo "Read batch\n";
            
            return $batchdata;
            
        } catch (Exception $e) {
            if ($this->debug) echo "Bad template\n";
        }
        
        // Retrieve 1 at a time
        $batchdata = [];
        
        foreach ($batchids as $pageid) {
            sleep(1);
            $temptdparams =$this->params;
            $temptdparams['pageids'] = $pageid;
            
            try {
                $ret = $this->mediawiki->getTemplateData($temptdparams);
                
                if (! empty($ret['pages'])) {
                    foreach ($ret['pages'] as $templid => $pagedata) {
                        $batchdata[$templid] = $pagedata;
                    }
                }
            } catch (Exception $e) {
                // NOP
            }
        }
        
        return $batchdata;
    }
}