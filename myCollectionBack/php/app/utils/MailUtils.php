<?php

namespace MyCollection\app\utils;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailUtils
{

    public static function sendMail(string $to, string $subject, string $bodyHtml, string $toName = null)
    {
//Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->Encoding = 'base64';
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->Port = 465;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

            //Enable SMTP authentication
            $mail->Username = 'wolfaryx@gmail.com';                     //SMTP username
            $mail->Password = SiteIniFile::instance()->getValue('mail', 'mailPwd');       //SMTP password

            //Recipients
            $mail->setFrom($mail->Username, SiteIniFile::instance()->getValue('mail', 'fromName', 'Aryx'));
            if ($toName != null) {
                $mail->addAddress($to, $toName);     //Add a recipient
            } else {
                $mail->addAddress($to);     //Add a recipient
            }

            $mail->CharSet = "UTF-8";


            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $bodyHtml;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            //echo 'Message has been sent';
        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }

}