<?php 
//        include GIGPRESS_PLUGIN_DIR . '/scripts/toggle-musicians.js';
 ?>
<script type="text/javascript">

jQuery(document).ready(function($) 
{
    // Initialize drag and drop
    $('#bc-sortable-cast').sortable({
        items: '.musician-cast-row',
        handle: '.drag-handle',
        axis: 'y',
        opacity: 0.8,
        placeholder: 'ui-state-highlight',
        start: function(e, ui) {
            ui.placeholder.css({
                'height': ui.item.outerHeight(),
                'background-color': '#f0f0f1',
                'border': '1px dashed #b4b9be',
                'margin-bottom': '6px'
            });
        },
        // This fires immediately when an item is dropped into a new position
        update: function(event, ui) {
            reindexMusicians();
        }
    });

    // Loop through rows in their current visible order and rewrite the weights
    function reindexMusicians() {
        $('.musician-cast-row').each(function(index) {
            // "index" starts at 0 for the top row and increments automatically
            $(this).find('.musician-order-input').val(index);
        });
    }

    // Connect into your "Show Saved Only" filter button seamlessly
    var showAllText = 'Show All Musicians';
    var showSelectedText = 'Show Selected Only';
    var showingSelectedOnly = false;

    $('#bc-toggle-saved').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        
        if (!showingSelectedOnly) {
            $('.musician-cast-row').each(function() {
                var $row = $(this);
                if (!$row.find('input[type="checkbox"]').is(':checked')) {
                    $row.hide();
                }
            });
            $button.text(showAllText).addClass('button-primary').removeClass('button-secondary');
            showingSelectedOnly = true;
        } else {
            $('.musician-cast-row').show();
            $button.text(showSelectedText).addClass('button-secondary').removeClass('button-primary');
            showingSelectedOnly = false;
        }
        
        // Refresh sortable structure maps so dragging hidden elements doesn't stutter
        $('#bc-sortable-cast').sortable('refresh');
    });

});
</script>