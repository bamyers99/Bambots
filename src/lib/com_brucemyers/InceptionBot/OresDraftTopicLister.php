<?php
/**
 Copyright 2018 Myers Enterprises II

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

namespace com_brucemyers\InceptionBot;

use com_brucemyers\MediaWiki\MediaWiki;
use com_brucemyers\Util\Logger;
use com_brucemyers\Util\Curl;
use Exception;

class OresDraftTopicLister
{
    protected $mediawiki;

    /**
     * Constructor
     *
     * @param $mediawiki MediaWiki wiki
     */
    public function __construct($mediawiki)
    {
        $this->mediawiki = $mediawiki;
    }

    /**
     * Get the ores scores
     *
     * @param $titles array page titles
     * @return array scores (title => score)
     * @throws Exception
     */
    public function getScores($titles)
    {
        foreach ($titles as $key => $title) {
            $titles[$key] = str_replace(' ', '_', $title);
        }

        $revisions = $this->mediawiki->getPagesLastRevision($titles);
        $revids = array();

        foreach ($titles as $title) {
            if (! isset($revisions[$title])) continue; // deleted/non-existant
            $revids[$revisions[$title]['revid']] = $title;
        }
        $scores = [];

        foreach ($revids as $revid => $title) {
            $URL = "https://api.wikimedia.org/service/lw/inference/v1/models/enwiki-drafttopic:predict";
            $trys = 0;

            while ($trys++ < 5) {
                $data = Curl::getUrlContents($URL, "{\"rev_id\": $revid}");
                if ($data === false) {
                    if ($trys == 5) {
                        throw new Exception("OresDraftTopicLister Problem reading $URL (" . Curl::$lastError . ")");
                    }
                    sleep($trys * 60);
                    continue;
                }

                $data = json_decode($data, true);

                if (is_null($data)) {
                    if ($trys == 5) {
                        throw new Exception("OresDraftTopicLister json_decode error for $URL");
                     }
                    sleep($trys * 60);
                    continue;
                }

                if (! isset($data['enwiki'])) {
                    if ($trys == 5) {
                        Logger::log(print_r($data, true));
                        throw new Exception("OresDraftTopicLister scores not set for $URL");
                    }
                    sleep($trys * 60);
                    continue;
                }

                break;
            }

            $data = $data['enwiki']['scores']["$revid"]['drafttopic']['score'];

            if (isset($data['probability'])) {
                $title = str_replace('_', ' ', $title);
                $scores[$title] = $data['probability'];
            }
        }

        return $scores;
    }
}