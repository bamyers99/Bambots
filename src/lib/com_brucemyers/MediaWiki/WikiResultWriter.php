<?php
/**
 Copyright 2013 Myers Enterprises II

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

namespace com_brucemyers\MediaWiki;

use com_brucemyers\Util\Logger;
use Exception;

class WikiResultWriter implements ResultWriter
{
    protected $mediawiki;

    /**
     * Constructor
     *
     * @param $mediawiki MediaWiki
     */
    public function __construct($mediawiki)
    {
        $this->mediawiki = $mediawiki;
    }

    /**
     * Write results
     *
     * @throws Exception
     */
    public function writeResults($resultpage, $output, $comment)
    {
        $ret = $this->mediawiki->edit($resultpage, $output, $comment);

        if (isset($ret['edit']['result']) && $ret['edit']['result'] != 'Success') {
            throw new Exception('Edit Error ' . Logger::log(print_r($ret, true)));
        } elseif (isset($ret['error'])) {
            throw new Exception('Edit Error ' . $ret['error']['info']);
        }
    }
}