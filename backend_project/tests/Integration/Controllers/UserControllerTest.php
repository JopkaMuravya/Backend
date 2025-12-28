<?php

namespace App\Tests\Integration\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SimpleUserControllerTest extends WebTestCase
{
    public function testProfileRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/profile');
        
        $this->assertResponseStatusCodeSame(401);
    }
    
    public function testRegisterEndpointExists(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/register');
        
        $this->assertResponseStatusCodeSame(400);
    }
}