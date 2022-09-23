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
	public function sendEmail($from, $to, $subject, $message, $attachments = [])
	{
	    preg_match('!@(.*)$!', $to, $matches);
	    $todomain = $matches[1];
	    	    
	    $fp = fopen('php://memory', 'r+');
	    $data = "From: <$from>\r\n";
	    $data .= "To: <$to>\r\n";
	    $data .= "Date: " . date('r') . "\r\n";
	    $data .= "Subject: $subject\r\n";
	    
	    // boundary
	    $semi_rand = md5(time());
	    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
	    
	    // headers for attachment
	    $data .= "MIME-Version: 1.0\r\n" .
	   	    "Content-Type: multipart/mixed;\r\n" .
	   	    " boundary=\"{$mime_boundary}\"\r\n";
	    
	    // multipart boundary
	    $data .= "--{$mime_boundary}\r\n" .
	    "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n" .
	    "Content-Transfer-Encoding: 7bit\r\n\r\n" . $message . "\r\n\r\n";
	    
	    // preparing attachment
	    foreach ($attachments as $attachment) {
	        $data .= "--{$mime_boundary}\r\n";
	        
	        $atchfp = @fopen($attachment,"rb");
	        $atchdata = @fread($atchfp,filesize($attachment));
	        @fclose($atchfp);
	        
	        $atchdata = chunk_split(base64_encode($atchdata));
	        
	        $data .= "Content-Type: application/octet-stream; name=\"".basename($attachment)."\"\r\n" .
	   	        "Content-Description: ".basename($attachment)."\r\n" .
	   	        "Content-Disposition: attachment;\r\n" .
	   	        " filename=\"".basename($attachment)."\"; size=".filesize($attachment).";\r\n" .
	   	        "Content-Transfer-Encoding: base64\r\n\r\n" . $atchdata . "\r\n\r\n";
	    }
	    
	    $data .= "--{$mime_boundary}--\r\n";
	    
	    fwrite($fp, $data);
	    rewind($fp);
	    
	    $ch = curl_init();
	    curl_setopt_array($ch, [
	        CURLOPT_URL => "smtp://mail.$todomain",
	        CURLOPT_MAIL_FROM => "<$from>",
	        CURLOPT_MAIL_RCPT => ["<$to>"],
	        CURLOPT_READFUNCTION => function($ch, $fp, $len){
	            return fread($fp, $len);
	        },
	        CURLOPT_INFILE => $fp,
	        CURLOPT_UPLOAD => true
	    ]);
	    
	    $success = curl_exec($ch);
	    
	    curl_close($ch);
	    fclose($fp);
	    
	    return $success;
	}
}
