<?php
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;

require_once __DIR__ . '/vendor/autoload.php';

$bot = new Nutgram(getenv('BOT_TOKEN'));
$bot->setRunningMode(Polling::class);

$bot->onText('My name is {name}', function(Nutgram $bot, string $name) {
    $bot->sendMessage("Hi $name");
});

$bot->onText('(.+)', function (Nutgram $bot, string $message) {
    $bot->sendMessage($message);
});

$bot->run();
