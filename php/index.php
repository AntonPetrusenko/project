<?php
include('vendor/autoload.php'); //Подключаем библиотеку

try {
    $bot = new \TelegramBot\Api\Client('375466075:AAEARK0r2nXjB67JiB35JCXXhKEyT42Px8s');

    $bot->command('start', function ($message) use ($bot) {
        $answer = 'Добро пожаловать!';
        $bot->sendMessage($message->getChat()->getId(), $answer);
    });

    $bot->run();
} catch (\Exception $exception)
{
    echo $exception->getMessage();
}