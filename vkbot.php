<?php

//include 'bd.php';
include 'func.php';

if (!isset($_REQUEST)) {
    return;
}

//Строка для подтверждения адреса сервера из настроек Callback API
$confirmation_token = '5ae8ab91';

//Ключ доступа сообщества
$token = '5ec86f4fa884d4e7564b5b164dd5c97b55171a673166abbe09a7e82353f6ea75e86bcc6606eff444071a5';

//Получаем и декодируем уведомление
$data = json_decode(file_get_contents('php://input'));



/*
 function msg_send($text){
     global $user_id, $token;
    $request_params = array(
        'message' => $text,
        'peer_id' => $user_id,
        'access_token' => $token,
        'v' => '5.103',
        'random_id' => '0'
    );
        $get_params = http_build_query($request_params);
        file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);
}

*/

//Проверяем, что находится в поле "type"
switch ($data->type) {
    //Если это уведомление для подтверждения адреса...
    case 'confirmation':
        //...отправляем строку для подтверждения
        echo $confirmation_token;
        break;


    case 'message_new':
        //получаем id пользователя
        $user_id = $data->object->message->from_id;
        //с помощью users.get получаем данные об пользователе
        $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&access_token={$token}&v=5.103"));

        //и извлекаем из ответа его имя
        $user_name = $user_info->response[0]->first_name;
        $text = $data->object->message->text;
        $date_msg = $data->object->message->date; //Дата в unixtime
        $date_msg_log = date("d M Y:H:i:s",$date_msg);


        $message = "
Имя: $user_name
Профиль: https://vk.com/id$user_id
Дата сообщения: $date_msg_log
Текст: $text
		";


        try{
            $time_record = date("d M Y:H:i:s");
            $file_name = "log_from_vk.log";
            $f = fopen($file_name, "a");
            flock ($f,2);
            fwrite ($f,"Записано в лог: $time_record\n$message");
            fclose($f);

        } catch(Exception $e) {}

        //Проверка: User пишет первый раз?
        $count = firstUserAuth($user_id);

        if ($count == 0) {

            createUser($user_id);

            $request_params = array(
                'message' => "{$user_name}, привет, я - бот!
                        Список доступных команд:
                        /help - список поддерживаемых команд,
                        /create - создать мероприятие,
                        /list - список всех мероприятий,
                        /mylist - список моих мероприятий,
                        /update - обновить мероприятие,
                        /delete <id> - удалить мероприятие",
                'peer_id' => $user_id,
                'access_token' => $token,
                'v' => '5.103',
                'random_id' => '0');

            } else { //тут уже команды

            $flag = getFlag($user_id);

            foreach ($flag as $row)
                $flag = htmlentities($row['0']);

            if (mb_substr($text, 0, 5) == "/list" and $flag == 'notCreate') { //

                $result = getAll();

                foreach ($result as $row) {
                    $id = htmlentities($row['0']);
                    $name = htmlentities($row['1']);
                    $description = htmlentities($row['2']);
                    $creator_id = htmlentities($row['3']);
                    $dat = htmlentities($row['4']);

                    //$dat = date("Y-m-d H:i:s");

                    //$answer = $answer . ' ' . "\n" . ' ' . $id . ' ' . $name . ' ' . $description . ' ' . $dat . ' ' . $creator_id;
                    $answer = $answer . ' ' . "\n"
                        . ' ' ."№: " . ' ' . $id . ' ' . "\n"
                        . ' ' ."Название: " . ' ' . $name . ' ' . "\n"
                        . ' ' ."Описание: " . ' ' . $description . ' ' . "\n"
                        . ' ' ."Дата: " . ' ' . $dat . ' ' . "\n"
                        . ' ' ."Создатель: " . ' ' . "https://vk.com/id". '' . $creator_id. ' ' . "\n";
                }

                $request_params = array(
                    'message' => "Список всех мероприятий: 
                    {$answer}",
                    'peer_id' => $user_id,
                    'access_token' => $token,
                    'v' => '5.103',
                    'random_id' => '0'
                );



            } else if (mb_substr($text, 0, 7) == "/mylist" and $flag == 'notCreate') { //

                $result = getMyList($user_id);

                foreach ($result as $row) {
                    $id = htmlentities($row['0']);
                    $name = htmlentities($row['1']);
                    $description = htmlentities($row['2']);
                    $creator_id = htmlentities($row['3']);
                    $dat = htmlentities($row['4']);

                    //$dat = date("Y-m-d H:i");

                    //$answer = $answer . ' ' . "\n" . ' ' . $id . ' ' . $name . ' ' . $description . ' ' . $creator_id . ' ' . $dat;

                    $answer = $answer . ' ' . "\n"
                        . ' ' ."№: " . ' ' . $id . ' ' . "\n"
                        . ' ' ."Название: " . ' ' . $name . ' ' . "\n"
                        . ' ' ."Описание: " . ' ' . $description . ' ' . "\n"
                        . ' ' ."Дата: " . ' ' . $dat . ' ' . "\n"
                        . ' ' ."Создатель: " . ' ' . "https://vk.com/id". '' . $creator_id. ' ' . "\n";
                }

                $request_params = array(
                    'message' => "Список ваших мероприятий: 
                    {$answer}",
                    'peer_id' => $user_id,
                    'access_token' => $token,
                    'v' => '5.103',
                    'random_id' => '0'
                );

            } else if (mb_substr($text, 0, 7) == "/create" and $flag == 'notCreate') { //

                updateFlag($user_id,'naming');

                $request_params = array(
                    'message' => "Введите название мероприятия:",
                    'peer_id' => $user_id,
                    'access_token' => $token,
                    'v' => '5.103',
                    'random_id' => '0'
                );


            } else if ($flag == 'naming') { //

                updateFlag($user_id,'describing');

                // создание мероприятия и вставка его названия
                createEvent($user_id,$text);

                $request_params = array(
                    'message' => "Напишите описание мероприятия:",
                    'peer_id' => $user_id,
                    'access_token' => $token,
                    'v' => '5.103',
                    'random_id' => '0'
                );

            }else if ($flag == 'describing') { //

                updateFlag($user_id,'dating');

                createDescription($user_id, $text);

                $request_params = array(
                    'message' => "Введите время мероприятия. Пример: 2022-04-25 17:45:00",
                    'peer_id' => $user_id,
                    'access_token' => $token,
                    'v' => '5.103',
                    'random_id' => '0'
                );
            }else if ($flag == 'dating') { //

                updateFlag($user_id,'notCreate');

                if(preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $text, $match)) {

                    $explode_data = explode(' ', $text);
                    $test_data = explode('-', $explode_data[0]);

                    if((@checkdate($test_data[1], $test_data[2], $test_data[0])) and ((strtotime($explode_data[1]) == true)) ) { // валидность даты и времени

                        createDate($user_id, $match[0]);

                        $request_params = array(
                            'message' => "Мероприятие успешно создано!",
                            'peer_id' => $user_id,
                            'access_token' => $token,
                            'v' => '5.103',
                            'random_id' => '0'
                        );
                    }else {
                        $request_params = array(
                        'message' => "Ошибка в дате! Обновите время мероприятия через команду /update ",
                        'peer_id' => $user_id,
                        'access_token' => $token,
                        'v' => '5.103',
                        'random_id' => '0'
                    );
                    }
                }else{
                    $request_params = array(
                        'message' => "Ошибка в дате! Обновите время мероприятия через команду /update ",
                        'peer_id' => $user_id,
                        'access_token' => $token,
                        'v' => '5.103',
                        'random_id' => '0'
                    );
                }
            }else if (mb_substr($text, 0, 7) == "/delete" and $flag == 'notCreate') {

                $delete_number = mb_substr($text, 8);

                $numb = selectEvent($delete_number);

                foreach ($numb as $row)
                    $numb = htmlentities($row['0']);

                if($user_id == $numb) {

                    deleteEvent($delete_number);

                    $request_params = array(
                        'message' => "Мероприятие удалено!",
                        'peer_id' => $user_id,
                        'access_token' => $token,
                        'v' => '5.103',
                        'random_id' => '0'
                    );

                }else{
                    $request_params = array(
                        'message' => "Мероприятие НЕ удалено! Вы не создатель мероприятия",
                        'peer_id' => $user_id,
                        'access_token' => $token,
                        'v' => '5.103',
                        'random_id' => '0'
                    );
                }
            }else if (mb_substr($text, 0, 7) == "/update" and $flag == 'notCreate') { // UPDATE

                updateFlag($user_id,'updating');

                $request_params = array(
                    'message' => "Введите id мероприятия:",
                    'peer_id' => $user_id,
                    'access_token' => $token,
                    'v' => '5.103',
                    'random_id' => '0'
                );

            }else if ($flag == 'updating') {

                $check= checkForUpdate($user_id, $text);


                if ($check == 1) {
                    updateFlag($user_id,'updatingID');

                    setUpdateID($user_id,$text);

                    $request_params = array(
                        'message' => "Что хотите обновить? Напишите: название/описание/дата",
                        'peer_id' => $user_id,
                        'access_token' => $token,
                        'v' => '5.103',
                        'random_id' => '0'
                    );
                } else {

                    updateFlag($user_id,'notCreate');

                    $request_params = array(
                        'message' => "Что-то пошло не так. Попробуй ещё раз!",
                        'peer_id' => $user_id,
                        'access_token' => $token,
                        'v' => '5.103',
                        'random_id' => '0'
                    );
                }

            }else if ($flag == 'updatingID') {
                switch ($text){
                    case 'название':
                        updateFlag($user_id,'updatingName');

                        $request_params = array(
                            'message' => "Введите название:",
                            'peer_id' => $user_id,
                            'access_token' => $token,
                            'v' => '5.103',
                            'random_id' => '0'
                        );
                        break;

                    case 'описание':
                        updateFlag($user_id,'updatingDesc');

                        $request_params = array(
                            'message' => "Введите описание:",
                            'peer_id' => $user_id,
                            'access_token' => $token,
                            'v' => '5.103',
                            'random_id' => '0'
                        );
                        break;

                    case 'дата':
                        updateFlag($user_id,'updatingData');

                        $request_params = array(
                            'message' => "Введите дату и время. Пример: 2022-04-25 17:45:00",
                            'peer_id' => $user_id,
                            'access_token' => $token,
                            'v' => '5.103',
                            'random_id' => '0'
                        );
                        break;

                    default:
                        updateFlag($user_id,'notCreate');

                        $request_params = array(
                            'message' => "Параметр не выбран. Попробуй ещё раз!",
                            'peer_id' => $user_id,
                            'access_token' => $token,
                            'v' => '5.103',
                            'random_id' => '0'
                        );
                        break;
                }

            }else if ($flag == 'updatingName' or $flag == 'updatingDesc' or $flag == 'updatingData' ) {

                $upd_id = getUpdateID($user_id);

                foreach ($upd_id as $row)
                    $upd_id = htmlentities($row['0']);

                switch ($flag){
                    case 'updatingName':

                        updateEvent('name',$text, $upd_id);



                        $request_params = array(
                            'message' => "Мероприятие обновлено!",
                            'peer_id' => $user_id,
                            'access_token' => $token,
                            'v' => '5.103',
                            'random_id' => '0'
                        );

                        break;

                    case 'updatingDesc':

                        updateEvent('description',$text, $upd_id);



                        $request_params = array(
                            'message' => "Мероприятие обновлено!",
                            'peer_id' => $user_id,
                            'access_token' => $token,
                            'v' => '5.103',
                            'random_id' => '0'
                        );

                        break;

                    case 'updatingData':
                        if(preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $text, $match))
                        {
                            $explode_data = explode(' ', $text);
                            $test_data = explode('-', $explode_data[0]);

                            if((@checkdate($test_data[1], $test_data[2], $test_data[0])) and ((strtotime($explode_data[1]) == true)) ) { // валидность даты и времени

                                updateEvent('date',$match[0], $upd_id);



                                $request_params = array(
                                    'message' => "Мероприятие успешно обновлено!",
                                    'peer_id' => $user_id,
                                    'access_token' => $token,
                                    'v' => '5.103',
                                    'random_id' => '0'
                                );
                            } else {

                                $request_params = array(
                                    'message' => "Мероприятие НЕ обновлено! Ошибка в дате",
                                    'peer_id' => $user_id,
                                    'access_token' => $token,
                                    'v' => '5.103',
                                    'random_id' => '0'
                                );
                            }
                        }else{

                            $request_params = array(
                                'message' => "Мероприятие НЕ обновлено! Ошибка в дате",
                                'peer_id' => $user_id,
                                'access_token' => $token,
                                'v' => '5.103',
                                'random_id' => '0'
                            );
                            break;
                        }

                }

                updateFlagNull($user_id);

            } else if (mb_substr($text, 0, 5) == "/help" and $flag == 'notCreate') {

                $request_params = array(
                    'message' => "Список доступных команд:
                        /help - список поддерживаемых команд,
                        /create - создать мероприятие,
                        /list - список всех мероприятий,
                        /mylist - список моих мероприятий,
                        /update - обновить мероприятие,
                        /delete <id> - удалить мероприятие",
                    'peer_id' => $user_id,
                    'access_token' => $token,
                    'v' => '5.103',
                    'random_id' => '0');

            } else {
                $request_params = array(
                    'message' => "Такой команды не существует! Чтобы узнать все комманды бота напишите: /help",
                    'peer_id' => $user_id,
                    'access_token' => $token,
                    'v' => '5.103',
                    'random_id' => '0'
                );
            }


        }

            $get_params = http_build_query($request_params);

            file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);


        //возвращаем серверу Callback API "ok"
        echo('ok');
        break;
        

}

?>