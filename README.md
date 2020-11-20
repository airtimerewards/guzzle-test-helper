# Guzzle Test Helper

This library is used to help test requests and responses made by guzzle. Instantiate an instance of `AirtimeRewards\GuzzleTestHelper\MockGuzzleClient` with the same parameters you would instantiate the standard guzzle client, then responses can be injected and requests can be retrieved for assertions.

## Example

```php
<?php

use AirtimeRewards\GuzzleTestHelper\MockGuzzleClient;

$client = new MockGuzzleClient();

$client->append(200, ['Content-Type' => 'text/plain'], 'Hello World!');
$client->append(204);

$response = $client->get('/foo');
echo $response->getBody(); // "Hello World!"
echo $response->getStatusCode(); // 200

$response2 = $client->post('/bar');
echo $response2->getStatusCode(); // 204

$request1 = $client->getRequest(0);
echo $request1->getUri(); // "/foo"
echo $request1->getMethod(); // "GET"

$request2 = $client->getLastRequest();
echo $request2->getUri(); // "/bar"
echo $request2->getMethod(); // "POST"
```
