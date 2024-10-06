<?php
/* 
Template Name: Webhook Settings
*/

get_header(); ?>

<div class="container">
    <h1>Webhook Settings</h1>

    <?php
    global $wpdb;
    $user_id = get_current_user_id();

    // Check if table ID is provided
    if (isset($_GET['table_id'])) {
        $table_id = intval($_GET['table_id']);

        // Fetch current webhook URL for this table
        $webhook_url = $wpdb->get_var($wpdb->prepare(
            "SELECT webhook_url FROM {$wpdb->prefix}user_tables WHERE id = %d AND user_id = %d",
            $table_id, $user_id
        ));

        if (!$webhook_url) {
            $webhook_url = ''; // If no webhook URL exists, start with an empty string
        }

        // Handle form submission to update webhook URL
        if (isset($_POST['update_webhook'])) {
            $new_webhook_url = esc_url_raw($_POST['webhook_url']);

            // Update the webhook URL in the database
            $wpdb->update("{$wpdb->prefix}user_tables", array('webhook_url' => $new_webhook_url), array('id' => $table_id));

            echo '<p>Webhook URL updated successfully!</p>';

            // Redirect to avoid resubmission
            wp_redirect(get_permalink() . '?table_id=' . $table_id);
            exit;
        }

        // Display the form for setting the webhook URL
        echo '<form method="post">';
        echo '<label for="webhook_url">Webhook URL:</label>';
        echo '<input type="url" name="webhook_url" value="' . esc_attr($webhook_url) . '" required><br><br>';
        echo '<input type="submit" name="update_webhook" value="Update Webhook">';
        echo '</form>';
    } else {
        echo '<p>No table selected for webhook settings.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
