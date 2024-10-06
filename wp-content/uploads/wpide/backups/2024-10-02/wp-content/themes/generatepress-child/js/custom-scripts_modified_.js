jQuery(document).ready(function($) {
    $('#batch-action-form').on('submit', function(e) {
        e.preventDefault();

        var action = '';
        if ($('button[name="delete_rows"]').is(':focus')) {
            action = 'delete_rows';
        } else if ($('button[name="duplicate_rows"]').is(':focus')) {
            action = 'duplicate_rows';
        }

        var selectedRows = [];
        $('input[name="selected_rows[]"]:checked').each(function() {
            selectedRows.push($(this).val());
        });

        if (selectedRows.length === 0) {
            alert('Please select at least one row.');
            return;
        }

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: action,
                table_id: "<?php echo $table_id; ?>",
                selected_rows: selectedRows
            },
            success: function(response) {
                alert(action === 'delete_rows' ? 'Rows deleted successfully!' : 'Rows duplicated successfully!');
                location.reload(); // Reload the page to reflect changes
            },
            error: function() {
                alert('An error occurred while processing the request.');
            }
        });
    });
});
