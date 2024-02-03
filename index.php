<?php

use Dotenv\Dotenv;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;

require_once __DIR__ . '/vendor/autoload.php';

class MessageException extends Exception { }

/**
 * @param string $message
 * @return void
 *
 * @throws MessageException
 */
function appendMessage(string $message): void
{
    if (empty($message)) {
        throw new MessageException('빈 메시지 저장은 허용하지 않습니다.');
    }

    $path = $_SERVER['STORE_PATH'] ?? $_ENV['STORE_PATH'] ?? false;
    $dir  = dirname($path);

    if (!file_exists($dir) || !is_dir($dir) || !is_executable($dir) || !is_writable($dir)) {
        throw new MessageException('저장 경로가 올바르지 않거나, 쓰기/접근 권한이 부족합니다.');
    }

    $fp = fopen($path, 'a');
    if (!$fp) {
        throw new MessageException('파일 열기에 실패했습니다.');
    }

    $now     = date_create_immutable('now', new DateTImeZone('Asia/Seoul'))->format('Y-m-d H:i:s');
    $message = htmlspecialchars(trim($message));
    $content = sprintf("[%s] %s\n", $now, $message);

    fwrite($fp, $content);
    fclose($fp);
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $bot = new Nutgram($_SERVER['BOT_TOKEN'] ?? $_ENV['BOT_TOKEN'] ?? '');
} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
    die($e->getMessage());
}

$bot->setRunningMode(Polling::class);

$bot->onText('My name is {name}', function (Nutgram $bot, string $name) {
    $bot->sendMessage("Hi $name");
});

$bot->onText('(.+)', function (Nutgram $bot, string $message) {
    try {
        appendMessage($message);
        // $bot->sendMessage('메시지를 성공적으로 저장했습니다!');
    } catch (MessageException $e) {
        $bot->sendMessage('메시지 저장 실패: ' . $e->getMessage());
    }
});

try {
    $bot->run();
} catch (ContainerExceptionInterface|NotFoundExceptionInterface $e) {
    die($e->getMessage());
}
