<?php

$xml = file_get_contents('https://raw.githubusercontent.com/Newls/glpi-dummy-plugin/master/index.xml');
$xml = simplexml_load_string($xml);

if (isset($xml->screenshots)){
    foreach((array) $xml->screenshots->screenshot as $url) {
        var_dump($url);
    }
}