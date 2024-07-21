<?php
/**
 * Template Name: Full Width
 *
 * @package Academica
 */

ob_start();

define('WP_USE_THEMES', false);
require('/var/www/wordpress/wp-blog-header.php');

// 启用错误报告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 检查用户是否为管理员
if (!current_user_can('administrator')) {
    ob_end_clean(); // 清空缓冲区
    $redirect_to = $_SERVER['REQUEST_URI'];
    wp_safe_redirect(wp_login_url($redirect_to));
    exit;
}

// 结束输出缓冲区并输出缓冲内容
ob_end_flush();

get_header(); ?>
<!-- <link rel="stylesheet" type="text/css" href="css/chat.css"> -->
<style>
    .user-message {
        background-color: #DCF8C6; /* User message background color */
        align-self: flex-end; /* 用戶對話向右對齊 */
        text-align: right;
        height: 50px;
    }

    .bot-message {
        background-color: #E8E8E8; /* Bot message background color */
        align-self: flex-start; /* bot對話向左對齊 */
        height: 50px;
    }

    .chat-container {
        display: flex;
        flex-direction: column;
    }
</style>
<?php
// echo do_shortcode('[members_access role="subscriber"]這段內容只有擁有網站管理員的用戶可以看到。[/members_access]');
?>

<div class="chat-container">
    <div class="chat-header">
        <h2>Chat</h2>
    </div>
    <div class="chat-message" id="chatForm" style="height: 800px;overflow: auto;padding: 10px;border:2px solid black;">
        <div class="bot-message">
            你好，需要什麼服務嗎?
        </div>
        <br />
        <div name="chatMessage" id="chatMessage"></div>
        <!-- <div class="user-message">
            這是用戶的一句話的範例。
        </div> -->
    </div>
    <br />
    <div class="chat-form">
        <form>
            <textarea name="message" id="msgBox" placeholder="輸入訊息..." style="width: 92%"></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
</div>

<?php get_footer(); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function scrollToBottom() {
        var chatMessageDiv = $('#chatForm');
        chatMessageDiv.scrollTop(chatMessageDiv[0].scrollHeight);
    }

    $.ajax({ //進入頁面後第一次更新訊息
        url: 'http://140.128.122.24/wp-content/themes/academica/chatHandle.php',
        type: 'POST',
        data: {
            action: 'handle_read'
        },success: function(response) { 
            var rs = JSON.parse(response);
            var message = rs.text;
            // console.log(message);
            message.forEach(function(msg) {
                var parts = msg.split(':::');
                var role = parts[0];
                var text = parts[1]
                if(text != undefined) {
                    text = text.replace(/\n/g, '<br>'); //將換行符號轉換成 <br>
                }
                var msgDiv;

                if(role == 'user') {
                    msgDiv = '<div class="user-message">' + text + '</div><br />';
                } else if(role == 'bot') {
                    msgDiv = '<div class="bot-message">' + text + '</div><br />';
                }

                $('#chatMessage').append(msgDiv);
            });

            scrollToBottom(); //會自動捲到最下面
            $('#msgBox').val(''); // 清空输入框
        }
    });



    jQuery(document).ready(function($) {
    $('form').on('submit', function(event) { //使用者按下送出訊息按鈕
        event.preventDefault(); // 防止表單默認提交

        var message = $('textarea[name="message"]').val();
        $.ajax({ //去呼叫後端處理訊息輸入程式
            url: 'http://140.128.122.24/wp-content/themes/academica/chatHandle.php',
            type: 'POST',
            data: {
                action: 'handle_write',
                message: message
            },
            success: function(response) { //成功後更新介面的訊息
                var jsonResponse = JSON.parse(response);
                if (jsonResponse.status === 'success') {
                    //console.log('Message sent successfully');
                    $.ajax({
                        url: 'http://140.128.122.24/wp-content/themes/academica/chatHandle.php',
                        type: 'POST',
                        data: {
                            action: 'handle_read'
                        },success: function(response) { 
                            var rs = JSON.parse(response);
                            var message = rs.text;
                            // console.log(message);
                            $('#chatMessage').empty(); //要先刪除前面的訊息
                            message.forEach(function(msg) {
                                var parts = msg.split(':::');
                                var role = parts[0];
                                var text = parts[1]
                                if(text != undefined) {
                                    text = text.replace(/\n/g, '<br>'); //將換行符號轉換成 <br>
                                }
                                var msgDiv;

                                if(role == 'user') {
                                    msgDiv = '<div class="user-message">' + text + '</div><br />';
                                } else if(role == 'bot') {
                                    msgDiv = '<div class="bot-message">' + text + '</div><br />';
                                }

                                $('#chatMessage').append(msgDiv);
                            });

                            scrollToBottom(); //會自動捲到最下面
                            $('#msgBox').val(''); // 清空输入框
                        }
                    });
                } else {
                    console.log('Error: ' + jsonResponse.message);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error: ' + error);
            }
        });
    });
});
</script>