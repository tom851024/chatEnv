<?php
/**
 * Template Name: Leaflet Map
 *
 * @package Academica
 */

define('WP_USE_THEMES', false);
require('/var/www/wordpress/wp-blog-header.php');
get_header();

?>
<h1>空汙資料</h1>
<?php
    // 開始 WordPress 循環
    if (have_posts()) : 
        while (have_posts()) : the_post(); 
            // 顯示內容，包含短代碼
            the_content();
        endwhile; 
    endif;
    ?>

<?php get_footer(); ?>

<!-- 包含 Leaflet CSS 和 JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>