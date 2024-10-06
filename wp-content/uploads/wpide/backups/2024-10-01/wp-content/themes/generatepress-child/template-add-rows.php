<?php
/* 
Template Name: Add Rows to Table
*/

get_header(); ?>

<div class="container">
    <h1>Add Rows to Your Table</h1>

    <?php
    global $wpdb;
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $role = $user->roles[0];

    // Set row limits based on user role
    if ($role === 'subscriber') {
        $row_limit = 100; // Free users are limited to 100 rows per table
    } else {
        $row_limit = 1000; // Paid users have a higher row limit
    }

    // Check if a table ID is provided
    if (isset($_GET['table_id'])) {
        $table_id = intval($_GET['table_id']);
        
        // Count the number of rows in the table
        $row_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}user_table_{$table_id}"
        ));

        // Check if the user has exceeded their row limit
        if ($row_count >= $row_limit) {
            echo '<p>You have reached your row limit for this table. Upgrade to a paid plan to add more rows.</p>';
        } else {
            // Fetch column names for the table
            $columns = $wpdb->get_col($wpdb->prepare(
                "SELECT column_name FROM {$wpdb->prefix}user_table_columns WHERE table_id = %d",
                $table_id
            ));

            if (!$columns) {
                echo '<p>No columns found for this table.</p>';
                get_footer();
                exit();
            }

            // Handle form submission to insert a new row
            if (isset($_POST['add_row'])) {
                $data = array();
                foreach ($columns as $column) {
                    $data[sanitize_title($column)] = sanitize_text_field($_POST[sanitize_title($column)]);
                }

                // Insert data into the dynamic table
                $wpdb->insert("{$wpdb->prefix}user_table_{$table_id}", $data);

                echo '<p>Row added successfully!</p>';

                // Redirect to prevent form resubmission on page reload
                wp_redirect(get_permalink());
                exit;
            }

            // Display the form for adding data to the table
            echo '<form method="post">';
            foreach ($columns as $column) {
                echo '<label for="' . sanitize_title($column) . '">' . esc_html($column) . ':</label>';
                echo '<input type="text" name="' . sanitize_title($column) . '" required><br><br>';
            }
            echo '<input type="submit" name="add_row" value="Add Row">';
            echo '</form>';
        }
    } else {
        echo '<p>No table selected.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
