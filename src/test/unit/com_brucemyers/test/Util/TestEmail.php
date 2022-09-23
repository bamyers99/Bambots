<?php
/**
 Copyright 2022 Myers Enterprises II

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

namespace com_brucemyers\test\Util;

use com_brucemyers\Util\Email;
use com_brucemyers\Util\Config;
use UnitTestCase;

class TestEmail extends UnitTestCase
{

    public function testSendEmail()
    {
        $basedir = Config::get(Config::BASEDIR);
        $attachment  = $basedir . '/src/test/unit/com_brucemyers/test/Util/EmailAttach.txt';
        
        $email = new Email();
        $email->sendEmail('admin@brucemyers.com', 'test@brucemyers.com', 'Email test', 'See attachment', [$attachment]);
    }
}