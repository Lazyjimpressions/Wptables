<?php
// WordPress header
get_header();
?>

<div id="homepage-wrapper">
    <?php
    // Load the homepage.html content from the homepage folder
    $homepage_html = get_stylesheet_directory() . '/homepage/homepage.html';
    if (file_exists($homepage_html)) {
        echo file_get_contents($homepage_html);
    } else {
        echo '<p>Error: Homepage HTML not found.</p>';
    }
    ?>
</div>

<?php
// WordPress footer
get_footer();
?>
