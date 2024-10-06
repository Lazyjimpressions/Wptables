<?php
/* 
Template Name: Edit Row
*/

get_header(); ?>

<div class="container">
    <h1>Edit Row</h1>

    <?php
    global $wpdb;
    $user_id = get_current_user_id();

    if (isset($_GET['table_id']) && isset($_GET['edit_row'])) {
        $table_id = intval($_GET['table_id']);
        $row_id = intval($_GET['edit_row']);

        // Fetch columns for this table
        $columns = $wpdb->get_col($wpdb->prepare(
            "SELECT column_name FROM {$wpdb->prefix}user_table_columns WHERE table_id = %d",
            $table_id
        ));

        // Fetch the current row data
        $row_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}user_table_{$table_id} WHERE id = %d",
            $row_id
        ), ARRAY_A);

        if (!$columns || !$row_data) {
            echo '<p>Row not found or no columns available.</p>';
            get_footer();
            exit();
        }

        // Handle form submission to update the row
        if (isset($_POST['update_row'])) {
            $data = array();
            foreach ($columns as $column) {
                $data[sanitize_title($column)] = sanitize_text_field($_POST[sanitize_title($column)]);
            }

            // Update the row in the dynamic table
            $wpdb->update("{$wpdb->prefix}user_table_{$table_id}", $data, array('id' => $row_id));

            echo '<p>Row updated successfully!</p>';

            // Redirect to avoid resubmission
            wp_redirect(get_permalink() . '?table_id=' . $table_id);
            exit;
        }

        // Display the form with current row data
        echo '<form method="post">';
        foreach ($columns as $column) {
            $sanitized_column_name = sanitize_title($column);
            echo '<label for="' . $sanitized_column_name . '">' . esc_html($column) . ':</label>';
            echo '<input type="text" name="' . $sanitized_column_name . '" value="' . esc_attr($row_data[$sanitized_column_name]) . '" required><br><br>';
        }
        echo '<input type="submit" name="update_row" value="Update Row">';
        echo '</form>';
    } else {
        echo '<p>No row selected to edit.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
