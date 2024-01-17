<?php
 
// Токен
  const TOKEN = '5612194986:AAFI6Gj-_D9piQspjTh6AJn8mXtW1PxB6Go';

  // ID чата
  const CHATID = '-867507503';
 
  // Массив допустимых значений типа файла.
  $types = array('image/gif', 'image/png', 'image/jpeg', 'application/pdf');
 
  // Максимальный размер файла в килобайтах
  // 1048576; // 1 МБ
  $size = 1073741824; // 1 ГБ
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $fileSendStatus = '';
    $textSendStatus = '';
    $msgs = [];
   
    // Проверяем не пусты ли поля с именем и телефоном
    if (!empty($_POST['name']) && !empty($_POST['phone'])) {
        
        // Если не пустые, то валидируем эти поля и сохраняем и добавляем в тело сообщения. Минимально для теста так:
        $txt = "Заявка с сайта на химчистку" . "%0A";
        
        // Имя
        if (isset($_POST['name']) && !empty($_POST['name'])) {
            $txt .= "Имя отправителя: " . strip_tags(trim(urlencode($_POST['name']))) . "%0A";
        }
        
        // Номер телефона
        if (isset($_POST['phone']) && !empty($_POST['phone'])) {
            $txt .= "Телефон: " . strip_tags(trim(urlencode($_POST['phone']))) . "%0A";
        }

        // Тип
        if (isset($_POST['type']) && !empty($_POST['type'])) {
            $txt .= "Для чего хімчистка: " . strip_tags(trim(urlencode($_POST['type']))) . "%0A";
        }

        // Доп сервисы
        if (isset($_POST['service_1']) && !empty($_POST['service_1'])) {
            $txt .= "Дополнительно:" . strip_tags(trim(urlencode($_POST['service_1']))) . "%0A";
        }
        if (isset($_POST['service_2']) && !empty($_POST['service_2'])) {
            $txt .= "Дополнительно:" . strip_tags(trim(urlencode($_POST['service_2']))) . "%0A";
        }
        if (isset($_POST['service_3']) && !empty($_POST['service_3'])) {
            $txt .= "Дополнительно:" . strip_tags(trim(urlencode($_POST['service_3']))) . "%0A";
        }
        if (isset($_POST['service_4']) && !empty($_POST['service_4'])) {
            $txt .= "Дополнительно:" . strip_tags(trim(urlencode($_POST['service_4']))) . "%0A";
        }
        
        // Не забываем про тему сообщения
        if (isset($_POST['theme']) && !empty($_POST['theme'])) {
            $txt .= "Тема: " . strip_tags(urlencode($_POST['theme']));
        }
    
        $textSendStatus = @file_get_contents('https://api.telegram.org/bot'. TOKEN .'/sendMessage?chat_id=' . CHATID . '&parse_mode=html&text=' . $txt); 
    
        if ( isset(json_decode($textSendStatus)->{'ok'}) && json_decode($textSendStatus)->{'ok'} ) {
            if (!empty($_FILES['files']['tmp_name'])) {
            
                $urlFile =  "https://api.telegram.org/bot" . TOKEN . "/sendMediaGroup";
                
                // Путь загрузки файлов
                $path = $_SERVER['DOCUMENT_ROOT'] . '/telegramform/tmp/';
                
                // Загрузка файла и вывод сообщения
                $mediaData = [];
                $postContent = [
                    'chat_id' => CHATID,
                ];
            
                for ($ct = 0; $ct < count($_FILES['files']['tmp_name']); $ct++) {
                    if ($_FILES['files']['name'][$ct] && @copy($_FILES['files']['tmp_name'][$ct], $path . $_FILES['files']['name'][$ct])) {
                    if ($_FILES['files']['size'][$ct] < $size && in_array($_FILES['files']['type'][$ct], $types)) {
                        $filePath = $path . $_FILES['files']['name'][$ct];
                        $postContent[$_FILES['files']['name'][$ct]] = new CURLFile(realpath($filePath));
                        $mediaData[] = ['type' => 'document', 'media' => 'attach://'. $_FILES['files']['name'][$ct]];
                    }
                    }
                }
            
                $postContent['media'] = json_encode($mediaData);
            
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
                curl_setopt($curl, CURLOPT_URL, $urlFile);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postContent);
                $fileSendStatus = curl_exec($curl);
                curl_close($curl);
                $files = glob($path.'*');
                foreach($files as $file){
                    if(is_file($file))
                    unlink($file);
                }
            }
            echo 1;
        } else {
            echo 0;
            $log = date('Y-m-d H:i:s') . ' неудалось отправить форму';
            file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);

            // 
            // echo json_decode($textSendStatus);
        }
    } else {
        echo 0;
    }
} else {
  header("Location: /");
}
