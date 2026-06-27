<?php 
//        include GIGPRESS_PLUGIN_DIR . '/scripts/auto-toggle-musicians.js';
//
// Inject jQuery automation handlers cleanly into the WordPress admin footer pool.
//
?>
<script type="text/javascript">
    jQuery(document).ready(function($) 
    {
        // 1. AUTO-TOGGLE: Instrument changes affect Musician checkbox
        $(document).on('change', 'input[name*="cast_instruments"]', function() 
        {
            var $row = $(this).closest('.musician-cast-row');
            var $musicianCheckbox = $row.find('input[name="cast_musicians[]"]');
            var checkedInstrumentsCount = $row.find('input[name*="cast_instruments"]:checked').length;

            if (checkedInstrumentsCount > 0) 
            {
                // If at least one instrument is checked, the musician MUST be checked
                $musicianCheckbox.prop('checked', true);
                $row.css({
                    'border-color': '#ccd0d4',
                    'background-color': '#fff'
                });
            } 
            else 
            {
                // If zero instruments are checked, uncheck the musician to maintain data integrity
                $musicianCheckbox.prop('checked', false);
            }
        });

        // 2. AUTO-TOGGLE: Musician checkbox changes affect Instruments
        $(document).on('change', 'input[name="cast_musicians[]"]', function() 
        {
            var $row = $(this).closest('.musician-cast-row');
            
            if (!$(this).is(':checked')) 
            {
                // If a user explicitly unchecks a musician, wipe out their instrument selections too
                $row.find('input[name*="cast_instruments"]').prop('checked', false);
                $row.css({
                    'border-color': '#ccd0d4',
                    'background-color': '#fff'
                });
            }
        });

        // 3. VALIDATION: Intercept WordPress post submission
        $('#post').on('submit', function(e) 
        {
            var validationFailed = false;
            var $firstOffender = null;

            $('.musician-row').each(function() 
            {
                var $row = $(this);
                var $musicianCheckbox = $row.find('input[name="cast_musicians[]"]');

                if ($musicianCheckbox.is(':checked')) 
                {
                    var checkedInstrumentsCount = $row.find('input[name*="cast_instruments"]:checked').length;

                    if (checkedInstrumentsCount === 0) 
                    {
                        validationFailed = true;
                        $row.css({
                            'border-color': '#d63638',
                            'background-color': '#fcf0f1'
                        });
                        if (!$firstOffender)
                            $firstOffender = $row;
                    } 
                    else 
                    {
                        $row.css({
                            'border-color': '#ccd0d4',
                            'background-color': '#fff'
                        });
                    }
                }
            });

            if (validationFailed) 
            {
                e.preventDefault();
                
                // Reset standard WordPress admin saving states/spinners
                $('#publish').removeClass('button-primary-disabled');
                $('#save-post').removeClass('button-disabled');
                $('.spinner').removeClass('is-active');

                alert('Validation Error: Every musician selected for the cast must have at least one instrument assigned.');
                
                if ($firstOffender) {
                    $('html, body').animate({
                        scrollTop: $firstOffender.offset().top - 100
                    }, 400);
                }

                return false;
            }
        });

    });
</script>
<?php