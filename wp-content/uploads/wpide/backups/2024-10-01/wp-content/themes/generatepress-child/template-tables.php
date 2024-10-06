<?php
/* 
Template Name: Create Table Template
*/

get_header(); ?>

<div class="container">
    <h1>Create a New Table</h1>

    <?php
    // Handle form submission
    if (isset($_POST['create_table'])) {
        $table_name = sanitize_text_field($_POST['table_name']);
        $columns = array(
            array('name' => sanitize_text_field($_POST['column1_name']), 'type' => sanitize_text_field($_POST['column1_type'])),
            array('name' => sanitize_text_field($_POST['column2_name']), 'type' => sanitize_text_field($_POST['column2_type']))
            // Add more columns if needed
        );

        $user_id = get_current_user_id();
        $result = create_dynamic_table($user_id, $table_name, $columns);

        if (is_wp_error($result)) {
            echo '<p>Error: ' . $result->get_error_message() . '</p>';
        } else {
            echo '<p>Table "' . esc_html($table_name) . '" created successfully!</p>';
        }
    }
    ?>

    <form method="post" action="">
        <label for="table_name">Table Name:</label>
        <input type="text" name="table_name" required><br><br>

        <label for="column1_name">Column 1 Name:</label>
        <input type="text" name="column1_name" required>
        <label for="column1_type">Type:</label>
        <select name="column1_type">
            <option value="VARCHAR(255)">Text</option>
            <option value="INT">Number</option>
            <option value="DATE">Date</option>
        </select><br><br>

        <label for="column2_name">Column 2 Name:</label>
        <input type="text" name="column2_name" required>
        <label for="column2_type">Type:</label>
        <select name="column2_type">
            <option value="VARCHAR(255)">Text</option>
            <option value="INT">Number</option>
            <option value="DATE">Date</option>
        </select><br><br>

        <!-- Add more fields for additional columns if necessary -->

        <input type="submit" name="create_table" value="Create Table">
    </form>
</div>

<?php get_footer(); ?>
