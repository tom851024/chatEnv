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
                "input_string" => $message
            );
            $jsonData = json_encode($postData);
            curl_setopt($ch, CURLOPT_URL, "http://140.128.122.19:5070/process");
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
            $botText .= "bot:::" . $responseData['result'] . ":::::";
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

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'handle_write':
            handle_write();
            break;
        case 'handle_read':
            handle_read();
            break;
        default:
            break;
    }
}
// echo json_encode(array('status' => 'success', 'message' => 'Message received.'));
// phpinfo()
?>