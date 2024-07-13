<?php
/**
 * Template Name: Full Width
 *
 * @package Academica
 */

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

<div class="chat-container">
    <div class="chat-header">
        <h2>Chat</h2>
    </div>
    <div class="chat-message" name="chatMessage" style="height: 800px;overflow: auto;padding: 10px;border:2px solid black;">
        <div class="bot-message">
            你好，需要什麼服務嗎?
        </div>
        <br />
        <div class="user-message">
            這是用戶的一句話的範例。
        </div>
    </div>
    <br />
    <div class="chat-form">
        <form>
            <textarea name="message" placeholder="輸入訊息..." style="width: 90%"></textarea>
            <button type="submit">Send</button>
        </form>
    </div>
    <div class="chat-download" name="chatDownload" style="height: 100px;width: 90%;overflow: auto;padding: 10px;border:2px solid black;">
        這裡會放供下載的文件
    </div>
</div>

<?php get_footer(); ?>