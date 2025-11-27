<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SimpleSecurityControllerTest extends WebTestCase
{
    public function testLoginEndpointExists(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/login');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/login');

        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }
}
