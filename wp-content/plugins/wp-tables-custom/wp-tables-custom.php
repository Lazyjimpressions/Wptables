<?php
/*
Plugin Name: WP Tables Custom
Plugin URI: https://yourwebsite.com
Description: Custom functionality for dynamic table creation and management.
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com
*/

// Your plugin functionality here (table creation, data insertion, etc.)

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

    // Insert column data
    foreach ($columns as $column) {
        $wpdb->insert("{$wpdb->prefix}user_table_columns", array(
            'table_id' => $table_id,
            'column_name' => sanitize_text_field($column['name']),
            'column_type' => sanitize_text_field($column['type']),
            'created_at' => current_time('mysql')
        ));
    }

    // Create the actual MySQL table
    $sql = "CREATE TABLE {$wpdb->prefix}user_table_{$table_id} (id INT AUTO_INCREMENT PRIMARY KEY";
    foreach ($columns as $column) {
        $sql .= ", " . sanitize_title($column['name']) . " " . sanitize_text_field($column['type']);
    }
    $sql .= ");";

    $wpdb->query($sql);

    return $table_id;  // Return the ID of the newly created table
}
