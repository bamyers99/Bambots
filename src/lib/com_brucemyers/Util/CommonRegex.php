<?php
/**
 Copyright 2015 Myers Enterprises II

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

class CommonRegex
{
    const COMMENT_REGEX = '/<!--.*?-->/us';
    // This uses possessive quantifier *+ which doesn't do any backtracking
    const REFERENCESTUB_REGEX = '!<ref(?:(?:\s+\w+(?:\s*=\s*(?:"[^"]*+"|\'[^\']*+\'|[^\'">\s]+))?)+\s*|\s*)/>!usi';
    const REFERENCE_REGEX = '!<ref[^e].*?</ref>!usi'; // Ignore <reference/>
    const NOWIKI_REGEX = '!<\s*nowiki\s*>.*?<\s*/nowiki\s*>!usi';
    const CATEGORY_REGEX = '/\\[\\[\s*Category\s*:([^\\]]*+)\\]\\]/usi';
    const REDIRECT_REGEX = '!#REDIRECT\s*:?\s*\\[\\[!usi';
    const BR_REGEX = '!<\s*br\s*/?\s*>!usi';
    const NOINCLUDE_REGEX = '!<\s*noinclude\s*>.*?<\s*/noinclude\s*>!usi';
}