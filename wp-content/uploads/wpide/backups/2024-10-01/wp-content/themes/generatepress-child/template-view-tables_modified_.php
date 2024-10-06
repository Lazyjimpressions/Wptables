<?php
/* 
Template Name: View Single Table
*/

get_header(); ?>

<div class="container">
    <h1>View Table</h1>

    <?php
    global $wpdb;

    // Get table ID from URL parameters
    if (isset($_GET['table_id'])) {
        $table_id = intval($_GET['table_id']);

        // Fetch column names for the table
        $columns = $wpdb->get_col($wpdb->prepare(
            "SELECT column_name FROM {$wpdb->prefix}user_table_columns WHERE table_id = %d",
            $table_id
        ));

        // Fetch rows for the table
        $rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}user_table_{$table_id}", ARRAY_A);

        // Check if columns and rows exist
        if ($columns && $rows) {
            echo '<table class="wp-dynamic-table">';
            echo '<tr>';
            foreach ($columns as $column) {
                echo '<th>' . esc_html($column) . '</th>';
            }
            echo '</tr>';

            foreach ($rows as $row) {
                echo '<tr>';
                foreach ($columns as $column) {
                    $sanitized_column_name = sanitize_title($column);
                    echo '<td>' . esc_html($row[$sanitized_column_name]) . '</td>';
                }
                echo '</tr>';
            }

            echo '</table>';
        } else {
            echo '<p>No data available for this table.</p>';
        }
    } else {
        echo '<p>Table not found. Please select a valid table to view.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
