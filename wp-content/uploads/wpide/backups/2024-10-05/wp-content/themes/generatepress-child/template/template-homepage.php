<?php
/*
Template Name: Homepage Template
*/
get_header();
?>

<div class="homepage-wrapper">
    <?php
    // Load the HTML content of the homepage
    include get_stylesheet_directory() . '/homepage/homepage.html';
    ?>
</div>

<?php
get_footer();
