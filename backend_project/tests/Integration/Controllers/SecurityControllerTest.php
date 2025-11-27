<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controllers;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        
        $this->clearDatabase();
    }

    private function clearDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        
        $connection->executeStatement('SET session_replication_role = replica;');
        
        $tables = $connection->createSchemaManager()->listTableNames();
        foreach ($tables as $table) {
            $connection->executeStatement("DELETE FROM {$table}");
        }
        
        $connection->executeStatement('SET session_replication_role = origin;');
    }

    private function createTestUser(string $email, string $phone, string $password = 'password123'): User
    {
        $user = new User(
            $email,
            $phone,
            '',
            'Test',
            'User',
            ['ROLE_USER']
        );
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    public function testSuccessfulLogin(): void
    {
        $this->createTestUser('test1@example.com', '+79161111111');
        
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => '+79161111111',
                'password' => 'password123'
            ])
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('user', $response);
        $this->assertEquals('+79161111111', $response['user']['phone']);
        $this->assertEquals('test1@example.com', $response['user']['email']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->createTestUser('test2@example.com', '+79162222222');
        
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'phone' => '+79162222222',
                'password' => 'wrong_password'
            ])
        );

        $this->assertResponseStatusCodeSame(401);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
    }

    public function testLoginWithoutRequiredFields(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['phone' => '+79163333333'])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLogout(): void
    {
        $user = $this->createTestUser('logout@example.com', '+79164444444');
        
        $this->client->loginUser($user);

        $this->client->request('POST', '/api/logout');

         $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects();
        
        $this->client->request('GET', '/api/profile');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testLogoutWithoutAuthentication(): void
    {
        $this->client->request('POST', '/api/logout');
        $this->assertResponseStatusCodeSame(401);
    }
}