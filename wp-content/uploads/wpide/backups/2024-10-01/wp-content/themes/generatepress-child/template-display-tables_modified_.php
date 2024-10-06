<?php
/* 
Template Name: Display User Tables
*/

get_header(); ?>

<div class="container">
    <h1>Your Created Tables</h1>

    <?php
    global $wpdb;
    $user_id = get_current_user_id();

    // Fetch the list of tables created by this user
    $user_tables = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}user_tables WHERE user_id = %d",
        $user_id
    ));

    // Check if user has any tables
    if ($user_tables) {
        echo '<ul>';
        foreach ($user_tables as $table) {
            echo '<li>';
            echo esc_html($table->table_name);
            echo ' <a href="' . site_url('/rename-table/?table_id=' . $table->id) . '">Rename</a>';
            echo ' | <a href="' . site_url('/view-tables/?table_id=' . $table->id) . '">View</a>'; // Corrected View link
            echo ' | <a href="' . site_url('/data-summary/?table_id=' . $table->id) . '">Data Summary</a>'; // Corrected Data Summary link
            echo ' | <a href="' . site_url('/delete-table/?table_id=' . $table->id) . '">Delete</a>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>You have not created any tables yet.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
