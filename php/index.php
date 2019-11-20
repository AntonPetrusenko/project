<?php
include('vendor/autoload.php'); //Подключаем библиотеку

use TelegramBot\Api\Client;

try {
    $bot = new Client('804984712:AAGv-Mhl6dXbdqHkKC5gMytj9GFE1fb5aNQ');

    $bot->command('help', function ($message) use ($bot) {
        $bot->sendMessage($message->getChat()->getId(), $answer = 'Тут надо перечислить команды для работы с ботом');
    });

    $bot->command('start', function ($message) use ($bot) {

        $keyboard = new \TelegramBot\Api\Types\ReplyKeyboardMarkup(
            getKeyboardScheduleArray(),
            true
        );

        $bot->sendMessage(
            $message->getChat()->getId(),
            'Добро пожаловать! Чтобы предсказать результаты, выберите интересующий вас матч.',
            false,
            null,
            null,
            $keyboard
        );

    });

    $bot->command('schedule', function ($message) use ($bot) {
        $bot->sendMessage($message->getChat()->getId(), $answer = getScheduleText());
    });

    $bot->on(function($update) use ($bot) {

        $message = $update->getMessage();
        $mtext = $message->getText();
        $cid = $message->getChat()->getId();

        if (mb_stripos($mtext,' - ') !== false) {

            $mtext = trim($mtext);
            $resultArr = explode(' - ', $mtext);

            //TODO: обработка ошибок
            $command1 = $resultArr[0];
            $command2 = $resultArr[1];

            $output = sendRequestToPython($command1, $command2);

            sendMessageWithPredictedResult($output, $bot, $cid, $command1, $command2);
        }

    }, function () { return true; });

    $bot->run();

    echo "Бот запущен";
} catch (\TelegramBot\Api\Exception $e) {
    echo $e->getMessage();
}

function sendMessageWithPredictedResult($output, $bot, $cid, $command1, $command2)
{
    $outputWithoutQuotes = str_replace('"', '', $output);
    $predict = stripAllWhiteSpaces($outputWithoutQuotes);

    switch ($predict) {
        case '0':
            $bot->sendMessage($cid, 'Результат: будет ничья');
            break;
        case '-1':
            $bot->sendMessage($cid, "Результат: победит команда $command1");
            break;
        case '1':
            $bot->sendMessage($cid, "Результат: победит команда $command2");
            break;
        default:
            $bot->sendMessage($cid, 'Вы ввели неверные названия команд. Попробуйте еще раз');
            break;
    }
}

function stripAllWhiteSpaces($string)
{
    $string = preg_replace('/\s+/', '', $string);
    $string = htmlentities($string);
    $string = str_replace('&thinsp;', '', $string);
    return str_replace('&nbsp;', '', $string);
}

function sendRequestToPython($command1, $command2)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'python/footballResult/' . $command1 . '_' . $command2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function sendRequestToFootballSchedule()
{
    $leagueId = '148';
    $apiKey = 'f4b776cfec15210f86fad319274a06b16ad2c95fbb0e16ab60454de8f11eb748';
    $dateFrom = date('Y-m-d');
    $dateTo = (new DateTime($dateFrom))->modify('+ 1 week')->format('Y-m-d');

    $dataArray = [
        'action' => 'get_events',
        'from' => $dateFrom,
        'to' => $dateTo,
        'league_id' => $leagueId,
        'APIkey' => $apiKey
    ];

    $client = new \GuzzleHttp\Client();
    $response = $client->request(
        'GET',
        'apiv2.apifootball.com/',
        [
            'query' => $dataArray
        ]
    );

    //TODO: обработка ошибок
    return json_decode($response->getBody(), true);
}

function getScheduleText()
{
    $responseArray = sendRequestToFootballSchedule();

    $resultString = "На этой неделе играют:\n";
    foreach ($responseArray as $match)
    {
        $command1 = $match['match_hometeam_name'] ?? null;
        $command2 = $match['match_awayteam_name'] ?? null;
        //TODO: обработка ошибок
        $resultString = $resultString . "$command1 - $command2\n";
    }

    return $resultString;
}

function getKeyboardScheduleArray()
{
    $responseArray = sendRequestToFootballSchedule();

    $result = [];
    $rowResult = [];
    $i = 0;
    foreach ($responseArray as $match)
    {
        $command1 = $match['match_hometeam_name'] ?? null;
        $command2 = $match['match_awayteam_name'] ?? null;
        //TODO: обработка ошибок

        $rowResult[] = ["text" => "$command1 - $command2"];
        if ($i == 1) {
            $result[] = $rowResult;
            $rowResult = [];
            $i = 0;
        } else {
            $i++;
        }
    }

    return $result;
}