<?php

use Dotenv\Dotenv;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;
use SergiX44\Nutgram\RunningMode\Webhook;

require_once __DIR__ . '/vendor/autoload.php';

class MessageException extends Exception { }

function getEnvVar(string $key): string|null
{
    return $_SERVER[$key] ?? $_ENV[$key] ?? null;
}

/**
 * @param string $message
 *
 * @return void
 *
 * @throws MessageException
 */
function appendMessage(string $message): void
{
    if (empty($message)) {
        throw new MessageException('빈 메시지 저장은 허용하지 않습니다.');
    }

    $path = getEnvVar('STORE_PATH') ?? false;
    $dir  = dirname($path);

    if (!file_exists($dir) || !is_dir($dir) || !is_executable($dir)) {
        throw new MessageException('저장 경로가 올바르지 않거나, 쓰기/접근 권한이 부족합니다.');
    }

    if (file_exists($path) && !is_writable($path)) {
        throw new MessageException('파일이 존재하지만, 파일에 쓰기 권한이 없습니다.');
    }

    $fp = fopen($path, 'a');
    if (!$fp) {
        throw new MessageException('파일 열기에 실패했습니다.');
    }

    $is_markdown = str_ends_with($path, '.md');
    $content     = formatMessage($message, $is_markdown);

    fwrite($fp, $content);
    fclose($fp);
}

function formatMessage(string $message, bool $is_markdown): string
{
    $message = trim($message);
    $message = preg_replace('/\s+/', ' ', $message);
    $message = htmlspecialchars($message);

    if ($is_markdown) {
        $message = preg_replace('/(\[|#+)/', '\\\\1', $message);
    }

    return sprintf(
        "%s[%s] %s\r\n",
        $is_markdown ? '* \\' : '',
        date_create_immutable('now', new DateTImeZone('Asia/Seoul'))->format('Y-m-d H:i:s'),
        $message,
    );
}

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $token = getEnvVar('BOT_TOKEN') ?? '';
    $bot   = new Nutgram($token);
} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
    die($e->getMessage());
}

$runningMode = getEnvVar('RUNNING_MODE') ?? 'webhook';
if (!in_array($runningMode, ['polling', 'webhook'], true)) {
    $runningMode = 'webhook';
}

$bot->setRunningMode('polling' === $runningMode ? Polling::class : Webhook::class);

$bot->onMessage(function (Nutgram $bot) {
    $message = $bot->message()?->text;
    if ($message) {
        try {
            appendMessage($message);
        } catch (MessageException $e) {
            $bot->sendMessage('메시지 저장 실패: ' . $e->getMessage());
        }
    }
    $bot->sendMessage("메시지를 저장하였습니다.");
});

try {
    $bot->run();
} catch (ContainerExceptionInterface|NotFoundExceptionInterface $e) {
    die($e->getMessage());
}
