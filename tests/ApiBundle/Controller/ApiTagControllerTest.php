<?php

namespace Tests\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\ApiBundle\DataFixtures\ORM\LoadTagData;

/**
 * {@inheritdoc}
 */
class ApiTagControllerTest extends WebTestCase
{
    /**
     * TagController test
     */
    public function testCompleteScenario()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/tag');
        $this->assertEquals(200,
            $client->getResponse()->getStatusCode(),
            'Unexpected HTTP status code for GET /api/tag'
        );
        $this->assertTrue(
            $client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $client->getResponse()->headers
        );
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $total = $responseContent['total'];
        $this->assertTrue($total >= 0, 'Unexpected request body for GET /api/tag');

        $doctrine = $client->getContainer()->get('doctrine');
        $entityManager = $doctrine->getManager();

        $fixture = new LoadTagData();
        $fixture->load($entityManager);

        $crawler = $client->request('GET', '/api/tag');
        $this->assertEquals(200,
            $client->getResponse()->getStatusCode(),
            'Unexpected HTTP status code for GET /api/tag'
        );
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $newTotal = $responseContent['total'];
        $lastObject = reset($responseContent['objects']);
        $this->assertTrue($newTotal > $total, 'Unexpected request body for GET /api/tag');

        $crawler = $client->request('DELETE', '/api/tag/'.$lastObject['id']);
        $this->assertEquals(200,
            $client->getResponse()->getStatusCode(),
            'Unexpected HTTP status code for DELETE /api/tag/'.$lastObject['id']
        );

        $crawler = $client->request('GET', '/api/tag');
        $this->assertEquals(200,
            $client->getResponse()->getStatusCode(),
            'Unexpected HTTP status code for GET /api/tag'
        );
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $newTotal = $responseContent['total'];
        $this->assertTrue($newTotal == $total, 'Unexpected request body for GET /api/tag');
    }
}
