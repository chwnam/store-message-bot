<?php

use Dotenv\Dotenv;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;

if ('cli' !== php_sapi_name()) {
    die('이 스크립트는 CLI 전용입니다.');
}

require_once __DIR__ . '/vendor/autoload.php';

function getEnvVar(string $key): string|null
{
    return $_SERVER[$key] ?? $_ENV[$key] ?? null;
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $webhook   = getEnvVar('WEBHOOK_URL') ?? '';
    $token     = getEnvVar('BOT_TOKEN') ?? '';
    $webhookIp = getEnvVar('WEBHOOK_IP') ?? null;
    $bot       = new Nutgram($token);

    if (empty($webhook)) {
        die('Invalid WEBHOOK_URL!');
    }

    if (!$bot->setWebhook($webhook, ip_address: $webhookIp)) {
        die('setWebhook failed!');
    };
} catch (GuzzleException|JsonException|NotFoundExceptionInterface|ContainerExceptionInterface|TelegramException $e) {
    die($e->getMessage());
}

echo "Set webHook to $webhook\n";
exit(0);
