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

    // Fetch some existing rows to analyze data for AI suggestions
    $existing_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}user_table_{$table_id} LIMIT 5", ARRAY_A
    ));

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
        exit();
    }

    // Display the form for adding data to the table
    echo '<form method="post">';
    foreach ($columns as $column) {
        $sanitized_column_name = sanitize_title($column);

        // Generate AI suggestions based on existing rows
        $suggestion = '';
        if (!empty($existing_rows)) {
            $suggestion = $existing_rows[array_rand($existing_rows)][$sanitized_column_name];
        }

        echo '<label for="' . $sanitized_column_name . '">' . esc_html($column) . ':</label>';
        echo '<input type="text" name="' . $sanitized_column_name . '" placeholder="Suggested: ' . esc_attr($suggestion) . '" required><br><br>';
    }
    echo '<input type="submit" name="add_row" value="Add Row">';
    echo '</form>';

    ?>

</div>

<?php get_footer(); ?>
