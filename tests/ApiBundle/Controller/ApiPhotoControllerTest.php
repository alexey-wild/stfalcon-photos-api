<?php

namespace Tests\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * {@inheritdoc}
 */
class ApiPhotoControllerTest extends WebTestCase
{
    /**
     * TagController test
     */
    public function testCompleteScenario()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/api/photo');
        $this->assertEquals(200,
            $client->getResponse()->getStatusCode(),
            'Unexpected HTTP status code for GET /api/photo'
        );
        $this->assertTrue(
            $client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $client->getResponse()->headers
        );
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $total = $responseContent['total'];
        $this->assertTrue($total >= 0, 'Unexpected request body for GET /api/photo');

        $now = time();

        copy(__DIR__.'/photo.jpg', __DIR__.'/photo'.$now.'.jpg');

        $file = new UploadedFile(
            __DIR__.'/photo'.$now.'.jpg',
            'photo.jpg',
            'image/jpeg',
            4933
        );

        $crawler = $client->request(
            'POST',
            '/api/photo',
            ['tags' => ['tag1', 'tag2', 'tag3', 'Test'.$now]],
            ['file' => $file]
        );

        $this->assertEquals(200,
            $client->getResponse()->getStatusCode(),
            'Unexpected HTTP status code for POST /api/photo'
        );
        $this->assertTrue(
            $client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $client->getResponse()->headers
        );
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $object = $responseContent['object'];
        $createdId = $object['id'];
        $createdName = $object['originalName'];
        $this->assertEquals('photo.jpg', $createdName, 'Unexpected request body for POST /api/photo');

        $payload = json_encode(['tags' => ['tag1', 'tag2', 'tag3', 'Test'.$now, 'NewTest'.$now]]);
        $client->request('PUT', '/api/photo/'.$createdId, [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        $this->assertEquals(200,
            $client->getResponse()->getStatusCode(),
            'Unexpected HTTP status code for PUT /api/photo/'.$createdId
        );
        $this->assertTrue(
            $client->getResponse()->headers->contains('Content-Type', 'application/json'),
            $client->getResponse()->headers
        );
        $responseContent = json_decode($client->getResponse()->getContent(), true);
        $object = $responseContent['object'];
        $this->assertTrue(count($object['tags']) == 5, 'Unexpected request body for POST /api/photo/');

        $client->request('DELETE', '/api/photo/'.$createdId);
        $this->assertEquals(200,
            $client->getResponse()->getStatusCode(),
            'Unexpected HTTP status code for DELETE /api/photo/'.$createdId
        );
    }
}
