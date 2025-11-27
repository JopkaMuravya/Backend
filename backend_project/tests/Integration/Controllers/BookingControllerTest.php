<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controllers;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SimpleBookingControllerTest extends WebTestCase
{
    public function testBookingsRequireLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/bookings');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateBookingRequiresLogin(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/bookings');

        $this->assertResponseStatusCodeSame(401);
    }
}
