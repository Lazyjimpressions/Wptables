<?php
/* 
Template Name: Delete Table
*/

get_header(); ?>

<div class="container">
    <h1>Delete Table</h1>

    <?php
    global $wpdb;
    $user_id = get_current_user_id();

    // Check if table ID is provided
    if (isset($_GET['table_id'])) {
        $table_id = intval($_GET['table_id']);

        // Fetch the table
        $table = $wpdb->get_row($wpdb->prepare(
            "SELECT table_name FROM {$wpdb->prefix}user_tables WHERE id = %d AND user_id = %d",
            $table_id, $user_id
        ));

        if (!$table) {
            echo '<p>Table not found.</p>';
            get_footer();
            exit();
        }

        // Handle form submission to delete the table
        if (isset($_POST['delete_table'])) {
            // Drop the actual table from the database
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}user_table_{$table_id}");

            // Delete the table metadata
            $wpdb->delete("{$wpdb->prefix}user_tables", array('id' => $table_id));
            $wpdb->delete("{$wpdb->prefix}user_table_columns", array('table_id' => $table_id));

            echo '<p>Table deleted successfully!</p>';

            // Redirect to avoid resubmission
            wp_redirect(site_url('/view-tables/'));
            exit;
        }

        // Display the delete confirmation form
        echo '<form method="post">';
        echo '<p>Are you sure you want to delete the table "' . esc_html($table->table_name) . '"? This action cannot be undone.</p>';
        echo '<input type="submit" name="delete_table" value="Delete Table">';
        echo '</form>';
    } else {
        echo '<p>No table selected for deletion.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
