<?php
include('vendor/autoload.php'); //Подключаем библиотеку

try {
    $bot = new \TelegramBot\Api\Client('804984712:AAGv-Mhl6dXbdqHkKC5gMytj9GFE1fb5aNQ');

    $bot->command('start', function ($message) use ($bot) {
        $bot->sendMessage($message->getChat()->getId(), 'Добро пожаловать');

        $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup([[["text" => "/devanswer"], ["text" => "Chelsea"], ["text" => "Liverpool"]]], true, true);
        $bot->sendMessage($message->getChat()->getId(), "Выберете интересущий вас раздел", false, null,null, $keyboard);
    });

    $bot->command('help', function ($message) use ($bot) {
        $bot->sendMessage($message->getChat()->getId(), $answer = 'Тут надо перечислить команды для работы с ботом');
    });

    $bot->command('devanswer', function ($message) use ($bot) {
        preg_match_all('/{"text":"(.*?)",/s', file_get_contents('http://devanswers.ru/'), $result);
        $bot->sendMessage($message->getChat()->getId(),
            str_replace("<br/>", "\n", json_decode('"' . $result[1][0] . '"')));
    });

    $bot->on(function(\TelegramBot\Api\Types\Update $update) use ($bot) {

        $message = $update->getMessage();
        $mtext = $message->getText();
        $cid = $message->getFrom()->getId();

            $bot->sendMessage($cid, 'Первая команда "Chelsea"');
    });


    $bot->run();

    echo "Бот запущен";
} catch (\TelegramBot\Api\Exception $e) {
    $e->getMessage();
}