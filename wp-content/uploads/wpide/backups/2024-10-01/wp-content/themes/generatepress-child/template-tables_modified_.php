<?php
/* 
Template Name: Create Tables
*/

get_header(); ?>

<div class="container">
    <h1>Create a New Table</h1>

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

    // Count the number of tables created by the current user
    $user_table_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}user_tables WHERE user_id = %d",
        $user_id
    ));

    // Check if the user has exceeded their table limit
    if ($user_table_count >= $table_limit) {
        echo '<p>You have reached your table creation limit. Upgrade to a paid plan to create more tables.</p>';
    } else {
        // Handle form submission to create a new table
        if (isset($_POST['create_table'])) {
            $table_name = sanitize_text_field($_POST['table_name']);
            $columns = sanitize_text_field($_POST['columns']);

            // Validate the number of columns
            $column_array = explode(',', $columns);
            if (count($column_array) > 10) {
                echo '<p>Maximum of 10 columns are allowed.</p>';
            } else {
                // Insert table metadata
                $wpdb->insert("{$wpdb->prefix}user_tables", array(
                    'user_id' => $user_id,
                    'table_name' => $table_name
                ));

                // Get the newly created table ID
                $table_id = $wpdb->insert_id;

                // Create table in the database
                $sql = "CREATE TABLE {$wpdb->prefix}user_table_{$table_id} (id INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY(id))";
                foreach ($column_array as $column) {
                    $sanitized_column = sanitize_title($column);
                    $sql .= ", $sanitized_column VARCHAR(255)";
                }
                $sql .= ") ENGINE=InnoDB;";

                $wpdb->query($sql);

                // Insert column metadata
                foreach ($column_array as $column) {
                    $wpdb->insert("{$wpdb->prefix}user_table_columns", array(
                        'table_id' => $table_id,
                        'column_name' => sanitize_text_field($column)
                    ));
                }

                echo '<p>Table created successfully!</p>';
            }
        }

        // Display the form to create a new table
        echo '<form method="post">';
        echo '<label for="table_name">Table Name:</label>';
        echo '<input type="text" name="table_name" required><br><br>';

        echo '<label for="columns">Columns (comma separated):</label>';
        echo '<input type="text" name="columns" placeholder="e.g. name, age, email" required><br><br>';

        echo '<input type="submit" name="create_table" value="Create Table">';
        echo '</form>';
    }
    ?>

</div>

<?php get_footer(); ?>
