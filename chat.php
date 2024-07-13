<?php
get_header(); 
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <div id="chat-container">
            <!-- 這裡將包含聊天窗口 -->
            <?php echo do_shortcode('[wpbot]'); ?>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>