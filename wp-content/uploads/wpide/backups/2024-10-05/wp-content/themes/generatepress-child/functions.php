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
        array('wp-element'), // Added 'wp-element' to provide React and ReactDOM.
        null,
        true // Load script in the footer
    );

    // Enqueue custom styles for the homepage
    if (is_front_page() || is_home()) {
        wp_enqueue_style(
            'homepage-styles',
            get_stylesheet_directory_uri() . '/style.css'
        );
    }
}

// Add React root container to WordPress pages
function add_react_root_container() {
    if (is_front_page()) {
        echo '<div id="react-root"></div>'; // Add React root container for homepage
    }
}
add_action('wp_footer', 'add_react_root_container');


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