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
use \API\Core\Mailer;
use \Illuminate\Database\Capsule\Manager as DB;
use \API\Model\Message;
use \ReCaptcha\ReCaptcha;
use \API\OAuthServer\OAuthHelper;
use \API\Exception\InvalidField;
use \API\Exception\MissingField;
use \API\Exception\InvalidRecaptcha;

require dirname(__FILE__) . '/../../config.php';

$send = Tool::makeEndpoint(function() use($app) {
   OAuthHelper::needsScopes(['message']);

   $msg_alerts_settings = Tool::getConfig()['msg_alerts'];
   $body = Tool::getBody();
   $fields = ['firstname', 'lastname', 'email', 'subject', 'message'];

   $recaptcha = new ReCaptcha(Tool::getConfig()['recaptcha_secret']);
   $resp = $recaptcha->verify($body->recaptcha_response);
   if (!$resp->isSuccess()) {
      throw new InvalidRecaptcha();
   }

   foreach($fields as $prop) {
      if (!property_exists($body->contact, $prop)) {
         throw new MissingField($prop);
      }
      else {
         switch ($prop) {
            case 'email':
               if (gettype($body->contact->email) != 'string' ||
                   !filter_var($body->contact->email, FILTER_VALIDATE_EMAIL)) {
                  throw new InvalidField('email');
               }
               break;
            case 'firstname':
               if (gettype($body->contact->firstname) != 'string' ||
                   strlen($body->contact->firstname) > 45) {
                  throw new InvalidField('firstname');
               }
               break;
            case 'lastname':
               if (gettype($body->contact->lastname) != 'string' ||
                   strlen($body->contact->lastname) > 45) {
                  throw new InvalidField('lastname');
               }
               break;
            case 'subject':
               if (gettype($body->contact->subject) != 'string' ||
                   strlen($body->contact->subject) > 280) {
                  throw new InvalidField('subject');
               }
               break;
            case 'message':
               if (gettype($body->contact->message) != 'string' ||
                   strlen($body->contact->message) > 16000) {
                  throw new InvalidField('message');
               }
               break;
         }
      }
   }

   // Sending mail
   $mailer = new Mailer;
   $mailer->sendMail('user_message.html', Tool::getConfig()['msg_alerts']['local_admins'],
                     '[MSG] '.$body->contact->subject, ['firstname' => $body->contact->firstname,
                                                        'lastname'  => $body->contact->lastname,
                                                        'email'     => $body->contact->email,
                                                        'message'   => $body->contact->message],
                     [$body->contact->email => $body->contact->firstname . ' '.$body->contact->lastname]);

   // also saving message in database
   $message = new Message();
   $message->first_name = $body->contact->firstname;
   $message->last_name = $body->contact->lastname;
   $message->email = $body->contact->email;
   $message->subject = $body->contact->subject;
   $message->message = $body->contact->message;
   $message->sent = DB::raw('NOW()');
   $message->save();

   Tool::endWithJson([
      "success" => true
   ]);
});

// HTTP REST Map
$app->post('/message', $send);

$app->options('/message',function(){});
