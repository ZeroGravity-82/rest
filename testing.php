<?php

require __DIR__.'/vendor/autoload.php';

use Guzzle\Http\Client;

// create our http client (Guzzle)
$client = new Client('http://localhost:8082', array(
    'request.options' => array(
        'exceptions' => false,
    )
));

$data = [
    'nickname'     => 'Warrior'.rand(0,999),
    'avatarNumber' => 5,
    'tagLine'      => 'A tag line!',
];
$request = $client->createRequest('POST', '/api/programmers', null,
    json_encode($data));
$response = $request->send();

//$request = $client->createRequest('GET', $url);
//$response = $request->send();
//
//$request = $client->createRequest('GET', '/api/programmers');
//$response = $request->send();

echo $response;
echo "\n\n";
