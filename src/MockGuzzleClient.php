<?php

declare(strict_types=1);

namespace AirtimeRewards\GuzzleTestHelper;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class MockGuzzleClient extends Client
{
    /**
     * @var array[]
     */
    private $guzzleHistory = [];

    /**
     * @var MockHandler
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [])
    {
        $history = Middleware::history($this->guzzleHistory);
        $this->handler = new MockHandler();
        $handlerStack = HandlerStack::create($this->handler);
        $handlerStack->push($history);
        $config['handler'] = $handlerStack;
        parent::__construct($config);
    }

    /**
     * Generates a new response and adds it to the response stack.
     *
     * @param int                                  $status  Status code
     * @param array                                $headers Response headers
     * @param string|resource|StreamInterface|null $body    Response body
     * @param string                               $version Protocol version
     * @param string|null                          $reason  Reason phrase (when empty a default will be used based on the status code)
     */
    public function append(
        int $status = 200,
        array $headers = [],
        $body = null,
        string $version = '1.1',
        ?string $reason = null
    ): void {
        $this->appendResponse(new Response($status, $headers, $body, $version, $reason));
    }

    /**
     * Adds a response to the response stack.
     *
     * @param Response $response
     */
    public function appendResponse(ResponseInterface $response): void
    {
        $this->handler->append($response);
    }

    /**
     * Returns the mock handler that is being used in the guzzle client.
     */
    public function getHandler(): MockHandler
    {
        return $this->handler;
    }

    /**
     * Fetches a specific request by index (starting at 0).
     *
     * @param int $index the index of the request
     */
    public function getRequest(int $index): RequestInterface
    {
        if (!isset($this->guzzleHistory[$index])) {
            throw new \OutOfBoundsException('Request does not exist.');
        }

        return $this->guzzleHistory[$index]['request'];
    }

    /**
     * Fetches a specific response by index (starting at 0).
     *
     * @param int $index the index of the response
     */
    public function getResponse(int $index): ResponseInterface
    {
        if (!isset($this->guzzleHistory[$index])) {
            throw new \OutOfBoundsException('Response does not exist.');
        }

        return $this->guzzleHistory[$index]['response'];
    }

    /**
     * @return RequestInterface the last request made
     */
    public function getLastRequest(): RequestInterface
    {
        if (0 === \count($this->guzzleHistory)) {
            throw new \OutOfBoundsException('No requests have been made.');
        }

        return \end($this->guzzleHistory)['request'];
    }

    /**
     * @return ResponseInterface the last response sent
     */
    public function getLastResponse(): ResponseInterface
    {
        if (0 === \count($this->guzzleHistory)) {
            throw new \OutOfBoundsException('No responses have been set.');
        }

        return \end($this->guzzleHistory)['response'];
    }

    /**
     * @return RequestInterface[]
     */
    public function getAllRequests(): array
    {
        return \array_map(static function ($historyItem): RequestInterface {
            return $historyItem['request'];
        }, $this->guzzleHistory);
    }

    /**
     * @return ResponseInterface[]
     */
    public function getAllResponses(): array
    {
        return \array_map(static function ($historyItem): ResponseInterface {
            return $historyItem['response'];
        }, $this->guzzleHistory);
    }
}
