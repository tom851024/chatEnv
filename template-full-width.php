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
        background-color: #DCF8C6; /* 用户消息背景色 */
        text-align: right;
        padding: 10px; /* 添加内边距，使内容与边框之间有间距 */
        display: inline-block; /* 让元素根据内容自动扩展宽度 */
        margin-bottom: 10px;
        margin-left: auto; /* 左边自动填充剩余空间 */
        border-radius: 5px; /* 可选：添加圆角，使对话框看起来更美观 */
        max-width: 80%; /* 可选：设置最大宽度，防止对话框过宽 */
    }

    .bot-message {
        background-color: #E8E8E8; /* Bot message background color */
        align-self: flex-start; /* bot對話向左對齊 */
        /* height: 50px; */
        padding: 10px; /* 添加內邊距，使內容與邊框之間有間距 */
        display: inline-block; /* 讓元素根據內容自動擴展寬度 */
        margin-bottom: 10px;
    }

    .chat-container {
        display: flex;
        flex-direction: column;
    }

    .user-message-container {
        display: flex;
        justify-content: flex-end; /* 將內部內容推到右側 */
        width: 100%;
    }
</style>
<?php
// echo do_shortcode('[members_access role="subscriber"]這段內容只有擁有網站管理員的用戶可以看到。[/members_access]');
?>


<div class="chat-header">
    <h2>Chat</h2>
</div>
<div class="chat-container" id="chatForm" style="height: 800px;overflow: auto;padding: 10px;border:2px solid black;">
    <div class="bot-message" id="eleTest">
        你好，需要什麼服務嗎?
    </div>
    <br />
    <div name="chatMessage" class="chat-container" id="chatMessage"></div>
</div>
<br />
<div class="chat-form">
    <form>
        <textarea name="message" id="msgBox" placeholder="輸入訊息..." style="width: 92%"></textarea>
        <button type="submit" id="submitbtn">傳送</button>
        <img id="spinner" src="/wp-content/themes/academica/images/spinner.gif" alt="Loading" style="width: 5%">
    </form>
    <br />
    <button type="button" id="clear">清空</button>
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
    $("#spinner").hide();
    $('#msgBox').on('keydown', function(event) {
        if (event.key === "Enter" && !event.shiftKey) { // 監聽 Enter 鍵，不包括 Shift+Enter
            event.preventDefault(); // 防止默認的換行行為
            $('#submitbtn').click(); // 觸發送出按鈕的點擊事件
        }
    });

    $('#clear').click(function() { //清空按鈕動作
        $.ajax({
            url: 'http://140.128.122.24/wp-content/themes/academica/chatHandle.php',
            type: 'POST',
            data: {
                action: 'clear',
            },
            success: function(response) {
                $('#chatMessage').empty(); //清空訊息
            }
        })
    });

    $('form').on('submit', function(event) { //使用者按下送出訊息按鈕
        event.preventDefault(); // 防止表單默認提交
        tmpText = $('#msgBox').val();
        if(tmpText === '') { //如果文字是空的，就不會送出
            return;
        }

        // $("#submitbtn").attr("disabled", true);
        $("#submitbtn").hide();
        $("#spinner").show();
        $("#msgBox").attr("disabled", true);

        //先將使用者打的文字送出
        if(tmpText != undefined) {
            tmpText = tmpText.replace(/\n/g, '<br>'); //將換行符號轉換成 <br>
        }
        userText = '<div class="user-message">' + tmpText + '</div><br />'
        $('#chatMessage').append(userText);
        scrollToBottom(); //會自動捲到最下面

        var message = $('textarea[name="message"]').val();
        $('#msgBox').val(''); // 清空输入框
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
                            // $('#msgBox').val(''); // 清空输入框
                        }
                    });
                } else {
                    console.log('Error: ' + jsonResponse.message);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error: ' + error);
            },
            complete: function() {
                // 確保無論請求成功或失敗後都能恢復按鈕
                // $("#submitbtn").attr("disabled", false);
                $("#submitbtn").show();
                $("#spinner").hide();
                $("#msgBox").attr("disabled", false);
            }
        });
    });
});
</script>