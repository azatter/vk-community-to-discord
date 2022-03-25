<?php
include_once('classes/simple_html_dom.php');

https://discord.com/api/webhooks/956885370149171221/vm4kw2yqzqI4tQIdKG6XQPo99IW4j-dRHF47hQkuLK4qf8EqmCTgpaCaqkQShvHrCkkl
$curl = curl_init(
    "https://discordapp.com/api/webhooks/XXX"
);
$link = "team"; live_malinovka

$html = file_get_html('https://m.vk.com/'.$link);
$currentDate = date("Y-d-m H:i");

// Парсинг
$i = 0;
$posts = $html->find('.wall_item');
$result = array();
if (!empty($posts)) {
    foreach ($posts as $post) {

        // Смотрим последние 2 записи сообщества, чтобы учесть закреплённый пост
        if($i < 2){
            $msg = $post->find('.pi_text', 0)->plaintext;

            if($msg == NULL){
                $msg = $post->find('.poster__text', 0)->plaintext;
            }

            $author = $post->find('.pi_signed', 0)->plaintext;
            $date = $post->find('.wi_info a', 0);

            $imageToExtract = $post->find('.thumbs_map_helper a div', 0);
            $imageToExtractString = $imageToExtract->outertext;
            $imgURL = getURL($imageToExtractString);

            // Отбираем посты, которые были выложены сегодня
            // Нормализуем формат даты
            if ( (strpos($date,'сегодня в ') == true) || (strpos($date,'today at ') == true) ) {
                $time = substr($date->plaintext,-5); // время постинга
                $VKDate = date("Y-d-m ").$time; // полный вид даты (буфферная переменная для проверки свежих постов)
            }

        $i++;
        }

    }
}

echo " > latest post date: ".$VKDate."<br>";
echo " > current date: ".$currentDate;

$newPostCheck = similar_text($VKDate, $currentDate); // Сверяем дату последнего поста и текущую дату (с учётом времени)
//echo " [ ".$newPostCheck." ] ";

// Отправление новых постов в дискорд
if($newPostCheck == 16) {
    curl_setopt($curl, CURLOPT_POST, 1);
    if($msg != null){

        // Избавляемся от "Показать полностью"
        if (strpos($msg,'Expand text…') !== false) {
            str_replace('Expand text…', '', $msg);
        } elseif(strpos($msg,'Показать полностью…') !== false) {
            str_replace('Показать полностью…', '', $msg);
        }

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            "content" => (" ```css\n" . html_entity_decode($msg) . "```" . $imgURL),
            "username" => "Spiday Bot"
        )));
    } else {
        // Если пост содержит только изображение
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            "content" => ($imgURL),
            "username" => "Spiday Bot"
        )));
    }

    echo curl_exec($curl);
    echo "<br> > new post sent to discord channel!<br>";
} else {
    echo "<br> > no updates! zZz...<br>";
}

$html->clear();
unset($html);

// Функция для извлечения ссылки на изображение из html/css
function getURL($string){
    $arr = explode(" ", $string);

    if($arr[4] != null){
        $imgURL = substr($arr[4],4 ,-3);
    } else {
        $imgURL = substr($arr[3],4 ,-3);
    }

    return $imgURL;
}

?>


