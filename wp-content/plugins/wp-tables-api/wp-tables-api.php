<?php
/*
Plugin Name: WP Tables API
Description: REST API for managing dynamic tables in WP-Tables.com.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WPTablesAPI {
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route('wp-tables/v1', '/tables', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_table'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));

        register_rest_route('wp-tables/v1', '/tables/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_table'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));

        register_rest_route('wp-tables/v1', '/tables/(?P<id>\d+)', array(
            'methods' => 'PATCH',
            'callback' => array($this, 'update_table'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));

        register_rest_route('wp-tables/v1', '/tables/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_table'),
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));
    }

    public function create_table($request) {
        global $wpdb;

        $table_name = sanitize_text_field($request->get_param('table_name'));
        $columns = $request->get_param('columns'); // Assume this is an array of column names

        $wpdb->insert("{$wpdb->prefix}user_tables", array(
            'user_id' => get_current_user_id(),
            'table_name' => $table_name,
            'created_at' => current_time('mysql')
        ));

        $table_id = $wpdb->insert_id;

        $sql = "CREATE TABLE {$wpdb->prefix}user_table_{$table_id} (id INT AUTO_INCREMENT PRIMARY KEY";
        foreach ($columns as $column) {
            $column_name = sanitize_title($column['name']);
            $column_type = sanitize_text_field($column['type']);
            $sql .= ", {$column_name} {$column_type}";
        }
        $sql .= ") ENGINE=InnoDB;";

        $wpdb->query($sql);

        return rest_ensure_response(array('table_id' => $table_id));
    }

    public function get_table($request) {
        global $wpdb;
        $table_id = intval($request['id']);

        $columns = $wpdb->get_results($wpdb->prepare(
            "SELECT column_name, column_type FROM {$wpdb->prefix}user_table_columns WHERE table_id = %d",
            $table_id
        ), ARRAY_A);

        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}user_table_%d",
            $table_id
        ), ARRAY_A);

        return rest_ensure_response(array('columns' => $columns, 'rows' => $rows));
    }

    public function update_table($request) {
        global $wpdb;
        $table_id = intval($request['id']);
        $row_id = intval($request->get_param('row_id'));
        $column = sanitize_text_field($request->get_param('column'));
        $value = sanitize_text_field($request->get_param('value'));

        $wpdb->update("{$wpdb->prefix}user_table_{$table_id}", array(
            $column => $value
        ), array('id' => $row_id));

        return rest_ensure_response('Row updated');
    }

    public function delete_table($request) {
        global $wpdb;
        $table_id = intval($request['id']);

        // Delete metadata
        $wpdb->delete("{$wpdb->prefix}user_tables", array('id' => $table_id));
        $wpdb->delete("{$wpdb->prefix}user_table_columns", array('table_id' => $table_id));

        // Drop table
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}user_table_{$table_id}");

        return rest_ensure_response('Table deleted');
    }
}

new WPTablesAPI();
