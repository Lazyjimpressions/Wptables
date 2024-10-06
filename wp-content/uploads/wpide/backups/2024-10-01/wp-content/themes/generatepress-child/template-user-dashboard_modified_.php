<?php
/* 
Template Name: User Dashboard
*/

get_header(); ?>

<div class="container">
    <h1>Your Dashboard</h1>

    <?php
    global $wpdb;
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $role = $user->roles[0];

    // Set table and row limits based on user role
    if ($role === 'subscriber') {
        $table_limit = 1; // Free users can create only 1 table
        $row_limit = 100; // Free users are limited to 100 rows per table
    } else {
        $table_limit = 5; // Paid users can create up to 5 tables
        $row_limit = 1000; // Paid users have a higher row limit
    }

    // Fetch tables created by the user
    $user_tables = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}user_tables WHERE user_id = %d",
        $user_id
    ));

    // Display table statistics
    $user_table_count = count($user_tables);
    echo '<p>You have created ' . $user_table_count . ' out of ' . $table_limit . ' allowed tables.</p>';

    if ($user_tables) {
        echo '<table class="wp-user-dashboard">';
        echo '<tr><th>Table Name</th><th>Rows</th><th>Actions</th></tr>';

        foreach ($user_tables as $table) {
            // Count the number of rows in each table
            $row_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}user_table_{$table->id}"
            ));

            echo '<tr>';
            echo '<td>' . esc_html($table->table_name) . '</td>';
            echo '<td>' . esc_html($row_count) . '</td>';
            echo '<td>';
            echo '<a href="' . site_url('/rename-table/?table_id=' . $table->id) . '">Rename</a> | ';
            echo '<a href="' . site_url('/delete-table/?table_id=' . $table->id) . '">Delete</a> | ';
            echo '<a href="' . site_url('/view-tables/?table_id=' . $table->id) . '">View</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo '<p>You have not created any tables yet.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
