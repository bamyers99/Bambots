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

namespace com_brucemyers\Util;

/**
 * Timer
 */
class Timer
{
    protected $startTime;

    /**
     * Start timer
     */
    public function start()
    {
        $this->startTime = $this->getMicrotime();
    }

    /**
     * Stop timer
     *
     * @return array Keys: days, hours, minutes, seconds
     */
    public function stop()
    {
        $took = round(($this->getMicrotime() - $this->startTime), 0);
        $days = floor($took / 86400);
        $took -= $days * 86400;
        $hours = floor($took / 3600);
        $took -= $hours * 3600;
        $minutes = floor($took / 60);
        $took -= $minutes * 60;

        return array('days' => $days, 'hours' => $hours, 'minutes' => $minutes, 'seconds' => $took);
    }

    protected function getMicrotime()
    {
    	list($usec, $sec) = explode(' ', microtime());
    	return ((float)$usec + (float)$sec);
    }
}
