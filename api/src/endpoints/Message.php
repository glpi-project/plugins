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

require 'config.php';

$send = function() use($app, $recaptcha_secret) {
    $body = Tool::getBody();
    $fields = ['firstname', 'lastname', 'email', 'subject', 'message'];

    $recaptcha = new ReCaptcha($recaptcha_secret);
    $resp = $recaptcha->verify($body->recaptcha_response);
    if (!$resp->isSuccess()) {
       return  Tool::endWithJson([
            "error" => "Recaptcha not validated"
        ]);
    }

    foreach($fields as $prop) {
        if (!property_exists($body->contact, $prop))
            return  Tool::endWithJson([
                "error" => "Missing ". $prop
            ]);
    }

    $message = new Message();
    $message->first_name = $body->contact->firstname;
    $message->last_name = $body->contact->lastname;
    $message->email = $body->contact->email;
    $message->subject = $body->contact->subject;
    $message->message = $body->contact->message;
    $message->sent = DB::raw('NOW()');

    $message->save();
};

// HTTP REST Map
$app->post('/message', $send);