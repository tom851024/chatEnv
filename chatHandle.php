<?php
define('WP_USE_THEMES', false);
require('/var/www/wordpress/wp-blog-header.php');

function handle_write() { //處理訊息寫入
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $current_user_id = get_current_user_id(); //獲得當前用戶的ID
        if(!empty($current_user_id)) {
            $file = "/var/www/wordpress/chat/" . $current_user_id;
            $message = isset($_POST['message']) ? $_POST['message'] : '';
            if(!file_exists($file)) { //第一次建檔
                exec("touch " . $file);
                $text = "user:::" . $message . ":::::";
                file_put_contents($file, $text);
            } else { //已經有檔案
                $text = file_get_contents($file);
                $text .= "user:::" . $message . ":::::";
                file_put_contents($file, $text);
            }

            //POST資料到AI模型
            $ch = curl_init();
            $postData = array(
                "model" => "llama3_gptq",
                "messages" => array(
                    array(
                        "role" => "user",
                        "content" => $message
                    )
                ),
                "tools" => null,
                "do_sample" => true,
                "temperature" => 0.8,
                "top_p" => 0.9,
                "n" => 1,
                "max_tokens" => 1000,
                "stop" => null,
                "stream" => false
            );
            $jsonData = json_encode($postData);
            curl_setopt($ch, CURLOPT_URL, "http://140.128.122.19:8000/v1/chat/completions");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ));
            $response = curl_exec($ch);
            $responseData = json_decode($response, true);
            $rs = json_decode($response, true);
            $botText = file_get_contents($file);
            $outputText = $responseData['choices'][0]['message']['content'];
            $botText .= "bot:::" . $outputText . ":::::";
            file_put_contents($file, $botText);
            
            curl_close($ch);

            echo json_encode(array('status' => 'success', 'message' => 'Message received.'));
        }
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Invalid request.'));
    }
}

function handle_read() { //處理讀取訊息
    $current_user_id = get_current_user_id(); //獲得當前用戶的ID
    $file = "/var/www/wordpress/chat/" . $current_user_id;
    if(file_exists($file)) {
        $text = file_get_contents($file);
        $textArr = explode(":::::", $text);
        echo json_encode(array('status' => 'success', 'text' => $textArr));
    }
}

function clear() { //處理清空訊息
    $current_user_id = get_current_user_id(); //獲得當前用戶的ID
    $file = "/var/www/wordpress/chat/" . $current_user_id;
    $newName = "/var/www/wordpress/chat/" . $current_user_id . "_" . time(); 

    rename($file, $newName); //將原先檔名替換為 使用者代號_目前時間戳
}

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'handle_write':
            handle_write();
            break;
        case 'handle_read':
            handle_read();
            break;
        case 'clear':
            clear();
            break;
        default:
            break;
    }
}
// echo json_encode(array('status' => 'success', 'message' => 'Message received.'));
// phpinfo()
?>