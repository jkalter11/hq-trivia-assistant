<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use React\EventLoop\LoopInterface;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions.php';

$client = new GuzzleHttp\Client();
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$headers = [
    'User-Agent'    => 'hq-viewer/1.2.4 (iPhone; iOS 11.1.1; Scale/3.00)',
    'Authorization' => 'Bearer ' . getenv('HQ_BEARER_TOKEN'),
    'x-hq-client'   => 'iOS/1.2.4 b59',
];

$response = $client->get('https://api-quiz.hype.space/shows/now?type=hq&userId=' . getenv('HQ_USER_ID'), ['headers' => $headers]);
$response = \GuzzleHttp\json_decode($response->getBody(), true);

$nextShow = new DateTime($response['nextShowTime'] ?? 'now');
$nextShow->setTimezone(new DateTimeZone('America/Toronto'));

if (empty($response['broadcast']['socketUrl'])) {
    die('Broadcast ended. Next show on ' . $nextShow->format(DATE_COOKIE) . ' for ' . $response['nextShowPrize'] . '.');
}

$loop = React\EventLoop\Factory::create();

connect($response['broadcast']['socketUrl'], $headers, $loop, $client);

/**
 * @param string                         $url
 * @param array                          $headers
 * @param \React\EventLoop\LoopInterface $loop
 * @param \GuzzleHttp\Client             $client
 */
function connect(string $url, array $headers, LoopInterface $loop, Client $client)
{
    $url = str_replace('https', 'wss', $url);

    $reactConnector = new React\Socket\Connector($loop, [
        'dns'     => '8.8.8.8',
        'timeout' => 10,
    ]);
    $connector      = new Ratchet\Client\Connector($loop, $reactConnector);

    $connector($url, [], $headers)
        ->then(function (Ratchet\Client\WebSocket $conn) use ($client, $url, $headers, $loop) {
            echo "Connected." . PHP_EOL;
            on_connect($conn, $client, $url, $headers, $loop);
        }, function (\Exception $e) use ($loop) {
            echo "Could not connect: {$e->getMessage()}\n";
            $loop->stop();
        });
}

/**
 * @param \Ratchet\Client\WebSocket      $conn
 * @param \GuzzleHttp\Client             $client
 * @param string                         $url
 * @param array                          $headers
 * @param \React\EventLoop\LoopInterface $loop
 */
function on_connect(Ratchet\Client\WebSocket $conn, Client $client, string $url, array $headers, LoopInterface $loop)
{
    $conn->on(
        'message',
        function (\Ratchet\RFC6455\Messaging\MessageInterface $msg) use ($conn, $client, $loop) {

            // strstr to strip control characters placed in the websocket response by HQ, sneaky sneaky
            $message = json_decode(strstr($msg, '{'), true);

            if ($message['type'] === 'broadcastEnded' && !isset($message['reason'])) {
                $loop->stop();
                sleep(1);

                die('Broadcast ended');
            }
            if ($message['type'] === 'question') {

                if (isset($message['answers']) && $message['type'] === 'question') {
                    predict_answers(
                        build_answers($message['answers']),
                        $message['question'],
                        $client
                    );
                }
            }
        });

    // I haven't actually fired any
    $conn->on('close', function () use ($url, $headers, $loop, $client) {
        connect($url, $headers, $loop, $client);
        echo "Reconnecting..." . PHP_EOL . PHP_EOL;
    });
}

$loop->run();
