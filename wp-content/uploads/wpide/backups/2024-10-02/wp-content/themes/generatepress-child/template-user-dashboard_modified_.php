<?php
/* 
Template Name: User Dashboard (Enhanced)
*/

get_header(); ?>

<div class="container">
    <h1>Your Dashboard</h1>

    <?php
    global $wpdb;
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $role = $user->roles[0];

    // Set table limits based on user role
    if ($role === 'subscriber') {
        $table_limit = 1; // Free users
        $row_limit = 100; 
    } else {
        $table_limit = 5; // Paid users
        $row_limit = 1000;
    }

    // Fetch tables created by the user
    $user_tables = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}user_tables WHERE user_id = %d",
        $user_id
    ));

    $user_table_count = count($user_tables);
    echo '<p>You have created ' . $user_table_count . ' out of ' . $table_limit . ' allowed tables.</p>';

    if ($user_tables) {
        echo '<table class="wp-user-dashboard">';
        echo '<tr><th>Table Name</th><th>Rows</th><th>Actions</th></tr>';

        foreach ($user_tables as $table) {
            $row_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}user_table_%d", $table->id
            ));

            echo '<tr>';
            echo '<td>' . esc_html($table->table_name) . '</td>';
            echo '<td>' . esc_html($row_count) . '</td>';
            echo '<td>';
            echo '<div class="dropdown">';
            echo '<button class="dropbtn">Actions</button>';
            echo '<div class="dropdown-content">';
            echo '<a href="' . site_url('/view-tables/?table_id=' . $table->id) . '">View</a>';
            echo '<a href="' . site_url('/rename-table/?table_id=' . $table->id) . '">Rename</a>';
            echo '<a href="' . site_url('/delete-table/?table_id=' . $table->id) . '">Delete</a>';
            echo '<a href="' . site_url('/data-summary/?table_id=' . $table->id) . '">Data Summary</a>';
            echo '</div>';
            echo '</div>';
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
