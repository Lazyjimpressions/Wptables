<?php
/* 
Template Name: Rename Table
*/

get_header(); ?>

<div class="container">
    <h1>Rename Table</h1>

    <?php
    global $wpdb;
    $user_id = get_current_user_id();

    // Check if table ID is provided
    if (isset($_GET['table_id'])) {
        $table_id = intval($_GET['table_id']);

        // Fetch the table name
        $table = $wpdb->get_row($wpdb->prepare(
            "SELECT table_name FROM {$wpdb->prefix}user_tables WHERE id = %d AND user_id = %d",
            $table_id, $user_id
        ));

        if (!$table) {
            echo '<p>Table not found.</p>';
            get_footer();
            exit();
        }

        // Handle form submission to rename the table
        if (isset($_POST['rename_table'])) {
            $new_table_name = sanitize_text_field($_POST['new_table_name']);

            // Update the table name in the database
            $wpdb->update("{$wpdb->prefix}user_tables", array('table_name' => $new_table_name), array('id' => $table_id));

            echo '<p>Table renamed successfully!</p>';

            // Redirect to avoid resubmission
            wp_redirect(site_url('/view-tables/'));
            exit;
        }

        // Display the rename form
        echo '<form method="post">';
        echo '<label for="new_table_name">New Table Name:</label>';
        echo '<input type="text" name="new_table_name" value="' . esc_attr($table->table_name) . '" required><br><br>';
        echo '<input type="submit" name="rename_table" value="Rename Table">';
        echo '</form>';
    } else {
        echo '<p>No table selected for renaming.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
