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

    // Check if a table ID is provided
    if (isset($_GET['table_id'])) {
        $table_id = intval($_GET['table_id']);
        
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

            // Fetch the webhook URL for this table
            $webhook_url = $wpdb->get_var($wpdb->prepare(
                "SELECT webhook_url FROM {$wpdb->prefix}user_tables WHERE id = %d AND user_id = %d",
                $table_id, $user_id
            ));

            // Trigger the webhook if a webhook URL is set
            if (!empty($webhook_url)) {
                $response = wp_remote_post($webhook_url, array(
                    'method'    => 'POST',
                    'body'      => json_encode($data),
                    'headers'   => array('Content-Type' => 'application/json'),
                ));

                if (is_wp_error($response)) {
                    echo '<p>Webhook failed to trigger: ' . $response->get_error_message() . '</p>';
                } else {
                    echo '<p>Webhook triggered successfully!</p>';
                }
            }

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
    } else {
        echo '<p>No table selected.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
