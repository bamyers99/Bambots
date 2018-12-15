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

        $revChunks = array_chunk($revids, 100, true);
        $scores = array();

        foreach ($revChunks as $revChunk) {
            $URL = "https://ores.wikimedia.org/v2/scores/enwiki/drafttopic/?revids=" . implode('|', array_keys($revChunk));
            $trys = 0;

            while ($trys++ < 5) {
                $data = Curl::getUrlContents($URL);
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

                if (! isset($data['scores'])) {
                    if ($trys == 5) {
                        Logger::log(print_r($data, true));
                        throw new Exception("OresDraftTopicLister scores not set for $URL");
                    }
                    sleep($trys * 60);
                    continue;
                }

                break;
            }

            $data = $data['scores']['enwiki']['drafttopic']['scores'];

            foreach ($revChunk as $revid => $title) {
                if (isset($data[$revid]['probability'])) {
                    $title = str_replace('_', ' ', $title);
                    $scores[$title] = $data[$revid]['probability'];
                }
            }
        }

        return $scores;
    }
}