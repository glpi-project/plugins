<?php
/**
 * Plugin
 *
 * This REST module hooks on
 * following URLs
 *
 * /plugin
 * /plugin/popular
 * /plugin/trending
 * /plugin/star
 */


use \API\Core\Tool;
use \Illuminate\Database\Capsule\Manager as DB;
use \API\Model\Message;
use \ReCaptcha\ReCaptcha;
use \API\OAuthServer\OAuthHelper;

require dirname(__FILE__) . '/../../config.php';

$send = function() use($app) {
   OAuthHelper::needsScopes(['message:send']);

   $msg_alerts_settings = Tool::getConfig()['msg_alerts'];
   $body = Tool::getBody();
   $fields = ['firstname', 'lastname', 'email', 'subject', 'message'];

   $recaptcha = new ReCaptcha(Tool::getConfig()['recaptcha_secret']);
   $resp = $recaptcha->verify($body->recaptcha_response);
   if (!$resp->isSuccess()) {
      return  Tool::endWithJson([
         "error" => "Recaptcha not validated"
      ]);
   }

   foreach($fields as $prop) {
      if (!property_exists($body->contact, $prop)) {
         return  Tool::endWithJson(["error" => "Missing ". $prop]);
      }
   }

   // Preparing to send mail, making recipients string
   $recipients = ''; $i = 0;
   foreach ($msg_alerts_settings['recipients'] as $recipient) {
      if ($i > 0)
         $recipients .= ', ';
      $recipients .= $recipient;
      $i++;
   }

   // Sending mail
   mail($recipients,
      $msg_alerts_settings['subject_prefix'] . '[MSG] '. $body->contact->subject,
      $body->contact->message,
      "From: ".$body->contact->firstname." ".$body->contact->lastname." <".$body->contact->email.">");

   // also saving message in database
   $message = new Message();
   $message->first_name = $body->contact->firstname;
   $message->last_name = $body->contact->lastname;
   $message->email = $body->contact->email;
   $message->subject = $body->contact->subject;
   $message->message = $body->contact->message;
   $message->sent = DB::raw('NOW()');
   $message->save();

   return Tool::endWithJson([
      "success" => true
   ]);
};

// HTTP REST Map
$app->post('/message', $send);

$app->options('/message',function(){});
