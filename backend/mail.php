<?php

#================================#
#       TorrentTrader 3.8.3      #
#  http://torrenttrader.uk       #
#--------------------------------#
#       Created by M-Jay         #
#       Modified by MicroMonkey, #
#       Coco, Botanicar          #
#================================#


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/SMTP.php";
require_once __DIR__ . "/PHPMailer/Exception.php";

$GLOBALS["TTMail"] = new TTMail;

class TTMail {
    var $type;

    var $smtp_host;
    var $smtp_port = 25;
    var $smtp_ssl = false;
    var $smtp_auth = false;

    var $smtp_user;
    var $smtp_pass;

    function __construct () {
        GLOBAL $site_config;

        switch (strtolower($site_config["mail_type"])) {
            case "pear":
                $this->smtp_ssl = $site_config["mail_smtp_ssl"];
                $this->smtp_host = $this->smtp_ssl
                    ? "ssl://".$site_config["mail_smtp_host"]
                    : $site_config["mail_smtp_host"];

                $this->type      = "pear";
                $this->smtp_port = $site_config["mail_smtp_port"];
                $this->smtp_auth = $site_config["mail_smtp_auth"];
                $this->smtp_user = $site_config["mail_smtp_user"];
                $this->smtp_pass = $site_config["mail_smtp_pass"];

                if (!@include_once("Mail.php")) {
                    trigger_error("Config is set to use PEAR Mail but it is not installed.", E_USER_WARNING);
                    $this->type = "php";
                }
            break;

            case "smtp":
                $this->type      = "smtp";
                $this->smtp_host = $site_config["mail_smtp_host"];
                $this->smtp_port = $site_config["mail_smtp_port"];
                $this->smtp_ssl  = $site_config["mail_smtp_ssl"];
                $this->smtp_auth = $site_config["mail_smtp_auth"];
                $this->smtp_user = $site_config["mail_smtp_user"];
                $this->smtp_pass = $site_config["mail_smtp_pass"];
            break;

            case "php":
            default:
                $this->type = "php";
        }
    }

    function Send ($to, $subject, $message, $additional_headers = "", $additional_parameters = "") {
        GLOBAL $site_config;

        if (preg_match("!^From:(.*)!m", $additional_headers, $matches)) {
            $from = trim($matches[1]);
        } else {
            $from = $site_config["SITEEMAIL"];
        }

        $additional_headers = preg_replace("!^From:(.*)!m", "", $additional_headers);
        $additional_headers .= "\nFrom: $from\nReturn-Path: $from";
        $additional_headers = trim($additional_headers);
        $additional_headers = preg_replace("!\n+!", "\n", $additional_headers);

        switch ($this->type) {
            case "pear":
                $headers = array(
                    "From" => $from,
                    "Return-Path" => $from,
                    "To" => $to,
                    "Subject" => $subject
                );
                $params = array(
                    "host" => $this->smtp_host,
                    "port" => $this->smtp_port,
                    "auth" => $this->smtp_auth,
                    "username" => $this->smtp_user,
                    "password" => $this->smtp_pass
                );
                $smtp = Mail::Factory("smtp", $params);
                $smtp->send($to, $headers, $message);
            break;

            case "smtp":
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = $this->smtp_host;
                    $mail->SMTPAuth   = $this->smtp_auth;
                    $mail->Username   = $this->smtp_user;
                    $mail->Password   = $this->smtp_pass;
                    $mail->SMTPSecure = $this->smtp_ssl ? 'tls' : false;
                    $mail->Port       = $this->smtp_port;

                    $mail->setFrom($from, $site_config["SITENAME"]);
                    $mail->addAddress($to);
                    $mail->Subject = $subject;
                    $mail->Body    = $message;
                    
                    $mail->AltBody = strip_tags($message);
                    $mail->CharSet = "UTF-8";
                    $mail->isHTML(true);

                    $mail->send();
                } catch (Exception $e) {
                    trigger_error("SMTP Mail error: " . $e->getMessage(), E_USER_WARNING);
                }
            break;

            case "php":
            default:
                @mail($to, $subject, $message, $additional_headers, $additional_parameters);
            break;
        }
    }
}

function sendmail ($to, $subject, $message, $additional_headers = "", $additional_parameters = "") {
    $GLOBALS["TTMail"]->Send($to, $subject, $message, $additional_headers, $additional_parameters);
}
?>