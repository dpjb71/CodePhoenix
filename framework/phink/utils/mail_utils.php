<?php
/*
 * Copyright (C) 2019 David Blanchard
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
 
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Phink\Utils;

/**
 * Description of mails
 *
 * @author david
 */

class TMailUtils
{
    
    private static $_instance = null;

    public static function getInstance()
    {
        if(self::$_instance == null) {
            self::$_instance = new TMailUtils();
        }
        
        return self::$_instance;
    }
    
    public function send($sender, $recipient, $subject, $html, $attachments = '', $bcc = '')
    {
        $result = false;

        //require_once('Mail.php');
        //require_once('Mail/mime.php');

        if($sender == '') {
            $sender = "no-reply@e-puzzle.net"; 
        }
        //$text = 'Voir la partie HTML.';// Text version of the email
        $crlf = "\n";
        $headers = array(
        'From'=> $sender,
        'To'=> $recipient,    
        'Return-Path' => $sender,
        'Subject' => $subject
        );

        // Creating the Mime message
        $mime = new Mail_mime($crlf);

//        //require_once 'html2txt/html2txt.php';
        
//        $text = convert_html_to_text($html);
//        // Setting the body of the email
//        $mime->setTXTBody($text);
        
        $mime->setHTMLBody($html);

        // Add an attachment
        if ($attachments != '') {
            foreach($attachments as $attachment) {
                list($name, $file, $type) = $attachment;
                $mime->addAttachment($file, $type, $name, 0);
            }
        }

        // Set body and headers ready for base mail class
        $body = $mime->get();
        $headers = $mime->headers($headers);

        // SMTP authentication params
        $smtp_params["host"] = "smtp.magic.fr";
        $smtp_params["port"] = "25";
        $recipients = $recipient;

        if($bcc != '') {
            $recipients .= ", $bcc";
        }

        // Sending the email using smtp
        $mail =&Mail::factory("smtp", $smtp_params);
        $result = $mail->send($recipients, $headers, $body);

        if (PEAR::isError($mail)) {
            $result = $mail->getMessage();
        }

        if($result === true) $result = 1;


        return $result;
    }
    
    public function sendFull($sender, $recipient, $subject, $text = '', $html = '', $charset = '', $attachments = null, $bcc = '')
    {
        $result = false;

        //require_once('Mail.php');
        //require_once('Mail/mime.php');

        if($sender == '') {
            $sender = "no-reply@e-puzzle.net"; 
        }

        $crlf = "\n";
        $headers = array(
        'From'=> $sender,
        'To'=> $recipient,    
        'Return-Path' => $sender,
        'Subject' => $subject
        );

        // Creating the Mime message
        $mime = new Mail_mime($crlf);

        // Setting the body of the email
        if($text != '') {
            $mime->setTXTBody($text);
        }
        if($html != '') {
            $mime->setHTMLBody($html);
        }

        if($text == '' && $html == '' ) {
            throw new InvalidArgumentException("Aucun corps de mail fourni.");
        }

        // Add an attachment
        if ($attachments != '') {
            foreach($attachments as $attachment) {
                list($name, $file, $type) = $attachment;
                $mime->addAttachment($file, $type, $name, 0);
            }
        }

        // Set body and headers ready for base mail class
        if($charset != '') {
            $body = $mime->get(array('text_charset' => $charset));
        } else {
            $body = $mime->get();

        }

        $headers = $mime->headers($headers);

        // SMTP authentication params
        $smtp_params["host"] = "smtp.magic.fr";
        $smtp_params["port"] = "25";

        // Sending the email using smtp
        $mail = &Mail::factory("smtp", $smtp_params);
        $result = $mail->send($recipient, $headers, $body);

        if (PEAR::isError($mail)) {
            $result = "ERR:" . $mail->getMessage();
        }

        return $result;
    }
    
    
    public function sendRaw($sender, $recipient, $subject, $text = '', $charset = '', $attachments = null, $bcc = '')
    {
        $result = false;

        //require_once('Mail.php');
        //require_once('Mail/mime.php');

        if($sender == '') {
            $sender = "no-reply@e-puzzle.net"; 

        }

        $crlf = "\n";
        $headers = array(
        'From'=> $sender,
        'To'=> $recipient,    
        'Return-Path' => $sender,
        'Subject' => $subject
        );

        // Creating the Mime message
        $mime = new Mail_mime($crlf);

        // Setting the body of the email
        if($text != '') {
            $mime->setTXTBody($text);
        } else {
            throw new InvalidArgumentException("Aucun corps de mail fourni.");
        }

        // Add an attachment
        if ($attachments != '') {
            foreach($attachments as $attachment) {
                list($name, $file, $type) = $attachment;
                $mime->addAttachment($file, $type, $name, 0);
            }
        }

        // Set body and headers ready for base mail class
        if($charset != '') {
            $body = $mime->get(array('text_charset' => $charset));
        } else {
            $body = $mime->get();

        }

        $headers = $mime->headers($headers);

        // SMTP authentication params
        $smtp_params["host"] = "smtp.magic.fr";
        $smtp_params["port"] = "25";

        // Sending the email using smtp
        $mail = &Mail::factory("smtp", $smtp_params);
        $result = $mail->send($recipient, $headers, $body);

        if (PEAR::isError($mail)) {
            $result = $mail->getMessage();
        }

        return $result;
    }

}
