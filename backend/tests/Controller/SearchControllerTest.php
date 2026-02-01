<?php

namespace App\Tests\Controller;

use App\Controller\SearchController;
use App\Entity\Search;
use App\Service\ApiResponse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(SearchController::class)]
#[CoversClass(ApiResponse::class)]
#[CoversClass(Search::class)]
final class SearchControllerTest extends WebTestCase
{
    private function prepareDatabase(EntityManagerInterface $em): void
    {
        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testIndexReturnsSearches(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $em = $client->getContainer()->get('doctrine')->getManager();
        $this->prepareDatabase($em);

        $search = new Search();
        $search->setCity('Lyon');
        $search->setLatitude(45.75);
        $search->setLongitude(4.85);

        $em->persist($search);
        $em->flush();
        $em->clear(); // Important pour forcer la lecture depuis la BDD

        $client->request('GET', '/api/searches');

        self::assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);

        self::assertTrue($response['success']);
        self::assertNotEmpty($response['data']);
        self::assertEquals('Lyon', $response['data'][0]['city']);
    }

    public function testCreateSavesSearch(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $em = $client->getContainer()->get('doctrine')->getManager();
        $this->prepareDatabase($em);

        $client->request('POST', '/api/searches', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'city' => 'Marseille',
            'latitude' => 43.29,
            'longitude' => 5.37
        ]));

        self::assertResponseStatusCodeSame(201);

        $repository = $em->getRepository(Search::class);
        $savedSearch = $repository->findOneBy(['city' => 'Marseille']);

        self::assertNotNull($savedSearch);
        self::assertEquals(43.29, $savedSearch->getLatitude());
    }

    public function testCreateDuplicateFails(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $em = $client->getContainer()->get('doctrine')->getManager();
        $this->prepareDatabase($em);

        $client->request('POST', '/api/searches', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'city' => 'Paris',
            'latitude' => 48.85,
            'longitude' => 2.35
        ]));
        self::assertResponseStatusCodeSame(201);

        $client->request('POST', '/api/searches', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'city' => 'Paris',
            'latitude' => 33.66,
            'longitude' => -95.55
        ]));
        self::assertResponseStatusCodeSame(201);

        $client->request('POST', '/api/searches', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'city' => 'Paris',
            'latitude' => 48.85,
            'longitude' => 2.35
        ]));

        self::assertResponseStatusCodeSame(409);
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals('Cette ville est déjà dans vos favoris.', $response['message']);
    }

    public function testCreateFailsWithInvalidData(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $client->request('POST', '/api/searches', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'city' => '',
            'latitude' => 0,
            'longitude' => 0
        ]));

        self::assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertFalse($response['success']);
    }
}
