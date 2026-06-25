<?php 
//        include GIGPRESS_PLUGIN_DIR . '/scripts/toggle-musicians.js';
 ?>
<script type="text/javascript">
    jQuery(document).ready(function($) 
    {
        var showAllText = 'Show All Musicians';
        var showSelectedText = 'Show Selected Only';
        var showingSelectedOnly = false;

        $('#bc-toggle-saved').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            
            if (!showingSelectedOnly)
            {    // Hide any row where the checkbox is NOT checked
                $('.musician-cast-row').each(function() {
                    var $row = $(this);
                    if (!$row.find('input[type="checkbox"]').is(':checked')) {
                        $row.hide();
                    }
                });
                $button.text(showAllText).addClass('button-primary').removeClass('button-secondary');
                showingSelectedOnly = true;
            } 
            else
            {    // Bring back all rows
                $('.musician-cast-row').show();
                $button.text(showSelectedText).addClass('button-secondary').removeClass('button-primary');
                showingSelectedOnly = false;
            }
        });
    });
</script>