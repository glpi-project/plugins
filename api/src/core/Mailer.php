<?php

namespace API\Core;

use API\Core\Tool;

class Mailer {
   private $mailer;
   private $renderer;

   public function __construct() {
      switch (Tool::getConfig()['transport']) {
         case 'mail':
            $transport = \Swift_MailTransport::getInstance();
            break;
         case 'smtp':
            $transport = \Swift_SmtpTransport::newInstance(Tool::getConfig()['msg_alerts']['smtp_server'],
                                                           Tool::getConfig()['msg_alerts']['port'],
                                                           Tool::getConfig()['msg_alerts']['smtp_transport_mode']);
            break;
         default:
            throw new \Exception('missing msg_alerts.transport setting. please see config.example.php and edit config.php accordingly');
      }

      if (isset(Tool::getConfig()['msg_alerts']['username'])) {
         $transport->setUsername(Tool::getConfig()['msg_alerts']['username']);
      }
      if (isset(Tool::getConfig()['msg_alerts']['password'])) {
         $transport->setPassword(Tool::getConfig()['msg_alerts']['password']);
      }

      $this->mailer = \Swift_Mailer::newInstance($transport);

      $loader = new \Twig_Loader_Filesystem(realpath(__DIR__ . '/../../mailtemplates/templates'));
      $this->renderer = new \Twig_Environment($loader, [
         'cache' => realpath(__DIR__ . '/../../mailtemplates/cache')
      ]);
   }

   public function sendMail($template, $to, $subject, $values, $replyTo = null) {
      $template = $this->renderer->loadTemplate($template);
      $values['client_url'] = Tool::getConfig()['client_url'];
      $values['subject'] = $subject;
      $mailBody = $template->render($values);

      $message = \Swift_Message::newInstance(Tool::getConfig()['msg_alerts']['subject_prefix'] ." ". $subject)
                               ->setFrom(Tool::getConfig()['msg_alerts']['from'])
                               ->setTo($to)
                               ->setBody($mailBody, 'text/html');

      if ($replyTo) {
         $message->setReplyTo($replyTo);
      }

      $this->mailer->send($message);
   }
}