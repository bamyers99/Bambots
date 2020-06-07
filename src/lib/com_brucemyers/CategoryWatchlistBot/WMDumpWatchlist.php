<?php
/**
 Copyright 2020 Myers Enterprises II

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

namespace com_brucemyers\CategoryWatchlistBot;

use com_brucemyers\Util\Config;
use com_brucemyers\Util\Curl;
use com_brucemyers\Util\FileCache;
use com_brucemyers\Util\MySQLDate;
use PDO;

class WMDumpWatchlist
{
    const QUERY_SAVE_DAYS = 'CategoryWatchlistBot.query_save_days';
    const CACHE_PREFIX_RESULT = 'DumpResult:';
    const CACHE_PREFIX_ATOM = 'DumpAtom:';

    public function process()
    {
        $serviceMgr = new ServiceManager();

        $asof_date = time();
        $mysql_asof_date = MySQLDate::toMySQLDatetime($asof_date);

        // Determine dump day
        $dateinfo = getdate($asof_date);

        if ($dateinfo['mday'] < 20) {
            $dumpday = 1;
            $current_reporttype = '1';
        } else {
            $dumpday = 20;
            $current_reporttype = '2';
        }

        $sqlmonth = $dateinfo['mon'];
        if ($sqlmonth < 10) $sqlmonth = "0$sqlmonth";

        $sqlday = $dumpday;
        if ($sqlday < 10) $sqlday = "0$sqlday";

        $dumpbefore = "{$dateinfo['year']}-$sqlmonth-$sqlday 00:00:00";

        // See if any statuses need to be retrieved
        $dbh_tools = $serviceMgr->getDBConnection('tools');
        $sth = $dbh_tools->prepare("SELECT * FROM dumpfiles WHERE (lastdump < ? OR lastdump IS NULL) AND lasterror = ''");
        $sth->bindParam(1, $dumpbefore);
        $sth->execute();

        $stalefiles = [];

        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $wikiname = $row['wikiname'];
            if (! isset($stalefiles[$wikiname])) $stalefiles[$wikiname] = [];
            $stalefiles[$wikiname][$row['filename']] = $row;
        }

        // Retrieve dump statuses

        foreach ($stalefiles as $wikiname => &$filerecs) {
            $url = "https://dumps.wikimedia.org/$wikiname/{$dateinfo['year']}{$sqlmonth}{$sqlday}/report.json";
            $retval = Curl::getUrlContents($url);
            $responseCode = Curl::$lastResponseCode;

            if ($retval === false || $responseCode != 200) {
                $errormsg = ($retval === false) ? Curl::$lastError : "HTTP status: $responseCode";

                $sth = $dbh_tools->prepare('UPDATE dumpfiles SET lasterror = ? WHERE wikiname = ?');
                $sth->bindParam(1, $errormsg);
                $sth->bindParam(2, $wikiname);
                $sth->execute();

                unset($stalefiles[$wikiname]);
                continue;
            }

            $retval = json_decode($retval, true)['jobs'];

            $fileprefixlen = strlen("$wikiname-{$dateinfo['year']}{$sqlmonth}{$sqlday}-");
            $validfilenames = [];

            foreach ($retval as $statfiles) {
                if (! isset($statfiles['files'])) continue;

                foreach ($statfiles['files'] as $filename => $statfile) {
                    $filename = substr($filename, $fileprefixlen);

                    $validfilenames[$filename] = true;

                    if (isset($statfile['size'])) {
                        foreach ($filerecs as &$filerec) {
                            if ($filename == $filerec['filename']) {
                                $sth = $dbh_tools->prepare('UPDATE dumpfiles SET lastdump = ?, filesize = ? WHERE wikiname = ? AND filename = ?');
                                $sth->bindParam(1, $mysql_asof_date);
                                $sth->bindParam(2, $statfile['size']);
                                $sth->bindParam(3, $wikiname);
                                $sth->bindParam(4, $filename);
                                $sth->execute();

                                $filerec['done'] = true;
                                break;
                            }
                        }

                        unset($filerec);
                    }
                }
            }

            // Error out file not founds
            foreach ($filerecs as $filerec) {
                $filename = $filerec['filename'];

                if (! isset($validfilenames[$filename])) {
                    $sth = $dbh_tools->prepare('UPDATE dumpfiles SET lasterror = ? WHERE wikiname = ? AND filename = ?');
                    $errormsg = 'File not found';
                    $sth->bindParam(1, $errormsg);
                    $sth->bindParam(2, $wikiname);
                    $sth->bindParam(3, $filename);
                    $sth->execute();
                }
            }
        }

        unset($filerecs);

        if (! empty($stalefiles)) {
            // Update last dump date on queries
            $sth = $dbh_tools->prepare("SELECT id, params FROM dumpquerys");
            $sth->execute();

            $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
            $usedfiles = [];

            foreach ($rows as $row) {
                $params = unserialize($row['params']);

                for ($x=1; $x <= 10; ++$x) {
                    $wikiname = $params["wn$x"];
                    $filename = $params["fn$x"];
                    if (empty($wikiname) || empty($filename)) break;
                    $reporttype = $params["rt$x"];

                    $filekey = "$wikiname\t$filename";
                    $usedfiles[$filekey] = true;

                    if (isset($stalefiles[$wikiname][$filename]['done']) && ($reporttype == 'B' || $reporttype == $current_reporttype)) {
                        $sth = $dbh_tools->prepare('UPDATE dumpquerys SET lastdump = ? WHERE id = ?');
                        $sth->bindParam(1, $mysql_asof_date);
                        $sth->bindParam(2, $row['id']);
                        $sth->execute();
                    }
                }
            }

            // Clear the cache, order is important
            FileCache::purgeAllPrefix(self::CACHE_PREFIX_RESULT);
            FileCache::purgeAllPrefix(self::CACHE_PREFIX_ATOM);

            // Purge unused files
            $this->purge_unused_files($serviceMgr, $usedfiles);
        }

        // Purge old queries
        $dbh_tools = $serviceMgr->getDBConnection('tools');
        $keepdays = Config::get(self::QUERY_SAVE_DAYS) + 1;
        $purgebefore = MySQLDate::toMySQLDatetime(strtotime("-$keepdays day"));
        $sth = $dbh_tools->prepare('SELECT id FROM dumpquerys WHERE lastaccess < ?');
        $sth->bindParam(1, $purgebefore);
        $sth->execute();

        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        $sth->closeCursor();

        foreach ($results as $row) {
            $id = $row['id'];
            if ($id == 1) continue; // Save sample
            $dbh_tools->exec("DELETE FROM dumpquerys WHERE id = $id");
        }
    }

    /**
     * Purge unused files
     *
     * @param ServiceManager $serviceMgr
     * @param array $usedfiles
     */
    public function purge_unused_files(ServiceManager $serviceMgr, $usedfiles)
    {
        $hour = date('G');
        if ($hour != 0) return; // once a day

        $dbh_tools = $serviceMgr->getDBConnection('tools');
        $sth = $dbh_tools->prepare("SELECT wikiname, filename FROM dumpfiles");
        $sth->execute();

        $rows = $sth->fetchAll(PDO::FETCH_ASSOC);

        $sth = $dbh_tools->prepare('DELETE FROM dumpfiles WHERE wikiname = ? AND filename = ?');

        foreach ($rows as $row) {
            $filekey = "{$row['wikiname']}\t{$row['filename']}";

            if (! isset($usedfiles[$filekey])) {
                $sth->bindParam(1, $row['wikiname']);
                $sth->bindParam(2, $row['filename']);
                $sth->execute();
            }
        }
    }
}