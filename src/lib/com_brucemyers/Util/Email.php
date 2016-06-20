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

class Email
{
	/**
	 * Send an email.
	 *
	 * @param string $from From email address
	 * @param string $to To email address
	 * @param string $subject Subject
	 * @param string $message Message
	 * @param array $attachments (optional) Full path to attachement
	 * @return bool true = succeeded
	 */
	public function sendEmail($from, $to, $subject, $message, $attachments = array())
	{
	    $headers = "From: <$from>" . "\r\n";

	    // boundary
	    $semi_rand = md5(time());
	    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

	    // headers for attachment
	    $headers .= "MIME-Version: 1.0\r\n" .
	    "Content-Type: multipart/mixed;\r\n" .
	    " boundary=\"{$mime_boundary}\"\r\n";

	    // multipart boundary
	    $message = "--{$mime_boundary}\r\n" .
	    "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n" .
	    "Content-Transfer-Encoding: 7bit\r\n\r\n" . $message . "\r\n\r\n";

	    // preparing attachment
	    foreach ($attachments as $attachment) {
		    $message .= "--{$mime_boundary}\r\n";
		    $fp =    @fopen($attachment,"rb");
		    $data =    @fread($fp,filesize($attachment));
		    @fclose($fp);
		    $data = chunk_split(base64_encode($data));
		    $message .= "Content-Type: application/octet-stream; name=\"".basename($attachment)."\"\r\n" .
		    "Content-Description: ".basename($attachment)."\r\n" .
		    "Content-Disposition: attachment;\r\n" .
		    " filename=\"".basename($attachment)."\"; size=".filesize($attachment).";\r\n" .
		    "Content-Transfer-Encoding: base64\r\n\r\n" . $data . "\r\n\r\n";
		    $message .= "--{$mime_boundary}--";
	    }

	    $success = mail($to, $subject, $message, $headers);
	    return $success;
	}
}
