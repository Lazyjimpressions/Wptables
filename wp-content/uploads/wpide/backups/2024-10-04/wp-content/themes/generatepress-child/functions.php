<?php
// Enqueue the parent theme's style.css
add_action('wp_enqueue_scripts', 'enqueue_parent_styles');
function enqueue_parent_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
}

// Enqueue custom scripts and styles for React App
add_action('wp_enqueue_scripts', 'enqueue_react_app_scripts');
function enqueue_react_app_scripts() {
    // Enqueue the React App's JavaScript bundle from the build directory
    wp_enqueue_script(
        'react-app-script',
        get_stylesheet_directory_uri() . '/react-app/build/bundle.js',
        array(), // Dependencies, you can add 'wp-element' if React is already available.
        null,
        true // Load script in the footer
    );

    // Enqueue the React App's CSS file from the build directory
    wp_enqueue_style(
        'react-app-style',
        get_stylesheet_directory_uri() . '/react-app/build/main.css'
    );

    // Enqueue the renamed JavaScript bundle in the child theme directory
    wp_enqueue_script(
        'bundle-child-script',
        get_stylesheet_directory_uri() . '/bundle-child.js',
        array(),
        null,
        true
    );
}

// Function to create dynamic tables for users
function create_dynamic_table($user_id, $table_name, $columns) {
    global $wpdb;

    // Check if the table already exists for this user
    $table_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}user_tables WHERE table_name = %s AND user_id = %d",
        $table_name,
        $user_id
    ));

    if ($table_exists) {
        return new WP_Error('table_exists', 'A table with this name already exists.');
    }

    // Insert new table metadata
    $wpdb->insert("{$wpdb->prefix}user_tables", array(
        'user_id' => $user_id,
        'table_name' => sanitize_text_field($table_name),
        'created_at' => current_time('mysql')
    ));

    $table_id = $wpdb->insert_id;

    // Create the actual MySQL table
    $sql = "CREATE TABLE {$wpdb->prefix}user_table_{$table_id} (id INT AUTO_INCREMENT PRIMARY KEY";
    foreach ($columns as $column) {
        $sql .= ", " . sanitize_title($column['name']) . " " . sanitize_text_field($column['type']);
    }
    $sql .= ") ENGINE=InnoDB;";
    $wpdb->query($sql);

    // Insert column data
    foreach ($columns as $column) {
        $wpdb->insert("{$wpdb->prefix}user_table_columns", array(
            'table_id' => $table_id,
            'column_name' => sanitize_text_field($column['name']),
            'column_type' => sanitize_text_field($column['type']),
            'created_at' => current_time('mysql')
        ));
    }

    return $table_id;  // Return the ID of the newly created table
}

// AJAX handler for inline row editing
add_action('wp_ajax_inline_edit', 'handle_inline_edit');
function handle_inline_edit() {
    global $wpdb;

    $table_id = intval($_POST['table_id']);
    $row_id = intval($_POST['row_id']);
    $column = sanitize_title($_POST['column']);
    $value = sanitize_text_field($_POST['value']);

    // Update the row in the dynamic table
    $wpdb->update("{$wpdb->prefix}user_table_{$table_id}", array(
        $column => $value
    ), array('id' => $row_id));

    wp_die(); // End the AJAX request
}

// Function to delete a row (for batch actions)
add_action('wp_ajax_delete_rows', 'handle_delete_rows');
function handle_delete_rows() {
    global $wpdb;

    $table_id = intval($_POST['table_id']);
    $selected_rows = $_POST['selected_rows'];

    foreach ($selected_rows as $row_id) {
        $wpdb->delete("{$wpdb->prefix}user_table_{$table_id}", array('id' => intval($row_id)));
    }

    wp_die(); // End the AJAX request
}

// Function to duplicate rows (for batch actions)
add_action('wp_ajax_duplicate_rows', 'handle_duplicate_rows');
function handle_duplicate_rows() {
    global $wpdb;

    $table_id = intval($_POST['table_id']);
    $selected_rows = $_POST['selected_rows'];

    foreach ($selected_rows as $row_id) {
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}user_table_%d WHERE id = %d",
            $table_id, $row_id
        ), ARRAY_A);

        // Remove the ID and insert as a new row
        unset($row['id']);
        $wpdb->insert("{$wpdb->prefix}user_table_{$table_id}", $row);
    }

    wp_die(); // End the AJAX request
}

// Enqueue custom scripts for batch actions
add_action('wp_enqueue_scripts', 'enqueue_custom_batch_scripts');
function enqueue_custom_batch_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-scripts', get_stylesheet_directory_uri() . '/js/custom-scripts.js', array('jquery'), null, true);
}

// Add React root container to WordPress pages
function add_react_root_container() {
    if (is_page_template('template-user-dashboard.php')) {
        echo '<div id="react-root"></div>'; // Add React root container for React app
    }
}
add_action('wp_footer', 'add_react_root_container');
