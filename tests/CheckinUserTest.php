<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CheckinUserTest extends ApiTestCase
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function testSomething(): void
    {
        $user = static::createClient()->request("POST", "/users/create", [
            "body" => json_encode(["email" => "test@test.nl"])
        ])->getContent();

        $this->assertResponseIsSuccessful("Created User");

        $membership = static::createClient()->request("POST", "/memberships/create", [
            'body' => json_encode([
                "start-date" => "2022-1-1",
                "end-date" => "2024-1-1",
                "user" => $user
            ])
        ])->toArray()["id"];

        $this->assertResponseIsSuccessful("Created membership");

        static::createClient()->request("POST", "/memberships/add-credits/".$membership."/1");

        $this->assertResponseIsSuccessful("Added 1 credit to membership");

        $checkin = static::createClient()->request("POST", "/checkin", [
            "body" => json_encode([
                "user" => $user,
                "gym" => "TestGym"
            ])
        ])->getContent();

        $this->assertStringContainsString("User successfully checked in", $checkin);

    }
}
