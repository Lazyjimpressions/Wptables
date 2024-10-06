<?php
/* 
Template Name: View Single Table (Enhanced)
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
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}user_table_{$table_id}", ARRAY_A
        ));

        // Check if columns and rows exist
        if ($columns && $rows) {
            echo '<form method="post" id="batch-action-form">';
            echo '<div class="batch-actions">';
            echo '<button type="submit" name="delete_rows" class="batch-btn">Delete Selected</button>';
            echo '<button type="submit" name="duplicate_rows" class="batch-btn">Duplicate Selected</button>';
            echo '</div>';

            echo '<table class="wp-dynamic-table">';
            echo '<thead><tr><th><input type="checkbox" id="select-all"></th>';
            foreach ($columns as $column) {
                echo '<th>' . esc_html($column) . '</th>';
            }
            echo '</tr></thead>';
            echo '<tbody>';

            foreach ($rows as $row) {
                echo '<tr>';
                echo '<td><input type="checkbox" class="select-row" name="selected_rows[]" value="' . esc_attr($row['id']) . '"></td>';
                foreach ($columns as $column) {
                    $sanitized_column_name = sanitize_title($column);
                    echo '<td contenteditable="true" data-column="' . esc_attr($sanitized_column_name) . '" data-row-id="' . esc_attr($row['id']) . '">';
                    echo esc_html($row[$sanitized_column_name]) . '</td>';
                }
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</form>';

            // Inline editing logic using AJAX
            ?>
            <script>
                document.querySelectorAll('td[contenteditable="true"]').forEach(function(cell) {
                    cell.addEventListener('blur', function() {
                        let column = cell.getAttribute('data-column');
                        let rowId = cell.getAttribute('data-row-id');
                        let newValue = cell.textContent;

                        let xhr = new XMLHttpRequest();
                        xhr.open("POST", "<?php echo admin_url('admin-ajax.php'); ?>", true);
                        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                        xhr.send("action=inline_edit&table_id=<?php echo $table_id; ?>&row_id=" + rowId + "&column=" + column + "&value=" + encodeURIComponent(newValue));
                    });
                });

                document.getElementById('select-all').addEventListener('change', function() {
                    let checkboxes = document.querySelectorAll('.select-row');
                    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                });
            </script>
            <?php
        } else {
            echo '<p>No data available for this table.</p>';
        }
    } else {
        echo '<p>Table not found. Please select a valid table to view.</p>';
    }
    ?>

</div>

<?php get_footer(); ?>
