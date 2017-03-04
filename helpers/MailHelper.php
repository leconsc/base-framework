<?php
/**
 * Email发送助手
 *
 * @author ChenBin
 * @version $Id: MailHelper.php, 1.0 2016-10-19 21:54+100 ChenBin$
 * @package: 3d
 * @since 1.0
 * @copyright 2016(C)Copyright By ChenBin, All rights Reserved.
 */

namespace Application\Helpers;


class MailHelper
{
    /**
     * 发送邮件.
     *
     * @param string $to
     * @param string $name
     * @param string $subject
     * @param string $message
     * @return bool
     * @throws \Exception
     */
    public static function send($to, $name, $subject, $message)
    {
        $di = \Phalcon\Di::getDefault();
        $config = $di->get('config');
        if (isset($config->mailer)) {
            $mailer = new \PHPMailer();
            $mailer->CharSet = "utf-8";
            switch ($config->mailer->mode) {
                case 'smtp':
                    $mailer->isSMTP();
                    $mailer->SMTPAuth = true;
                    if(isset($config->mailer->smtp->secure)){
                        $mailer->SMTPSecure = $config->mailer->smtp->secure;
                    }
                    $mailer->Host = $config->mailer->smtp->host;
                    $mailer->Port = $config->mailer->smtp->port;
                    $mailer->Username = $config->mailer->smtp->username;
                    $mailer->Password = $config->mailer->smtp->password;
                    break;
                case 'sendmail':
                    $mailer->isSendmail();
                    break;
                default:
                    break;
            }
            $mailer->setFrom($config->mailer->frommail, $config->mailer->fromuser);
            $mailer->addReplyTo($config->mailer->frommail, $config->mailer->fromuser);
            $mailer->addAddress($to, $name);
            $mailer->Subject = $subject;
            $mailer->msgHTML($message);
            $mailer->isHTML(true);

            if (!$mailer->send()) {
                throw new \Exception('Mailer Error: ' . $mailer->ErrorInfo);
            } else {
                return true;
            }
        }
    }
}