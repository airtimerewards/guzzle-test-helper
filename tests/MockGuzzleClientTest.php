<?php

declare(strict_types=1);

namespace AirtimeRewards\GuzzleTestHelper;

use PHPUnit\Framework\TestCase;

class MockGuzzleClientTest extends TestCase
{
    /** @var MockGuzzleClient */
    private $mockGuzzleClient;

    protected function setUp(): void
    {
        $this->mockGuzzleClient = new MockGuzzleClient(['http_errors' => false]);
    }

    public function testSuccessfulAppend(): void
    {
        $this->mockGuzzleClient->append(200, [], 'hello world!');
        $response = $this->mockGuzzleClient->request('GET', '/foo');
        $this->assertSame('hello world!', (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $request = $this->mockGuzzleClient->getRequest(0);
        $this->assertSame('/foo', (string) $request->getUri());
        $this->assertSame('GET', $request->getMethod());
    }

    public function testMultipleRequests(): void
    {
        $this->mockGuzzleClient->append(200, [], 'hello world!');
        $this->mockGuzzleClient->append(404, [], 'Not Found');
        $this->assertCount(0, $this->mockGuzzleClient->getAllRequests());
        $this->assertCount(0, $this->mockGuzzleClient->getAllResponses());

        $response1 = $this->mockGuzzleClient->request('GET', '/foo');

        $this->assertCount(1, $this->mockGuzzleClient->getAllRequests());
        $this->assertCount(1, $this->mockGuzzleClient->getAllResponses());

        $response2 = $this->mockGuzzleClient->request('POST', '/bar');

        $this->assertSame('hello world!', (string) $response1->getBody());
        $this->assertSame(200, $response1->getStatusCode());
        $request1 = $this->mockGuzzleClient->getRequest(0);
        $this->assertSame('/foo', (string) $request1->getUri());
        $this->assertSame('GET', $request1->getMethod());
        $this->assertSame('Not Found', (string) $response2->getBody());
        $this->assertSame(404, $response2->getStatusCode());
        $request2 = $this->mockGuzzleClient->getLastRequest();
        $this->assertSame('/bar', (string) $request2->getUri());
        $this->assertSame('POST', $request2->getMethod());
        $this->assertCount(2, $this->mockGuzzleClient->getAllRequests());
        $this->assertCount(2, $this->mockGuzzleClient->getAllResponses());
    }
}
