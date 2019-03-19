<?php

namespace KnpU\CodeBattle\Tests;

use Guzzle\Http\Client;

class ProgrammerControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testPOST()
    {
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

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $data = json_decode($response->getBody(true), true);
        $this->assertArrayHasKey('nickname', $data);
    }
}
