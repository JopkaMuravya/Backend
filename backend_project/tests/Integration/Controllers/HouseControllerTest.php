<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SimpleHouseControllerTest extends WebTestCase
{
    public function testHousesEndpointWorks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/houses');

        $this->assertResponseIsSuccessful();
    }

    public function testHousesReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/houses');

        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function testAvailableHousesEndpointWorks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/houses/available');

        $this->assertResponseIsSuccessful();
    }
}
