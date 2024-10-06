<?php
/* 
Template Name: Data Summary
*/

get_header(); ?>

<div class="container">
    <h1>Data Summary</h1>

    <?php
    global $wpdb;
    $user_id = get_current_user_id();
    $table_id = isset($_GET['table_id']) ? intval($_GET['table_id']) : 0;

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

    // Fetch all rows from the table
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}user_table_{$table_id}", ARRAY_A
    ));

    // Generate summary
    if (!empty($rows)) {
        echo '<h2>Summary of Data</h2>';
        echo '<ul>';
        
        // Summarize each column
        foreach ($columns as $column) {
            $sanitized_column = sanitize_title($column);
            $values = array_column($rows, $sanitized_column);
            $unique_values = array_unique($values);
            
            echo '<li>' . esc_html($column) . ': ';
            echo count($unique_values) . ' unique values, ';
            echo 'e.g. ' . esc_html(implode(', ', array_slice($unique_values, 0, 3))) . '...</li>';
        }

        echo '</ul>';
    } else {
        echo '<p>No data found in this table.</p>';
    }

    ?>

</div>

<?php get_footer(); ?>
