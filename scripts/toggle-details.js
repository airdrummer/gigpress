<!--
        $allow_initial_desktop_expand = true/false;
        include GIGPRESS_PLUGIN_DIR . '/js/toggle-details.js';
-->
<a id="toggle-all-cast" class="viewall button" 
            data-initial-expand="<?php echo $allow_initial_desktop_expand 
                                    ? 'true' : 'false'; ?>">
        Open/Close All Bios
</a>

<script type="text/javascript">

jQuery(document).ready(function($) 
{
    const $rows = $('details.cast-member-row');
    const $toggleBtn = $('#toggle-all-cast');
    const mediaQuery = window.matchMedia('(max-width: 668px)');
    
    // Read the boolean flag processed on the server by PHP
    const allowInitialExpand = $toggleBtn.data('initial-expand') === true;
    
    // 1. Initial Page Load Setup (Instant)
    if (allowInitialExpand && !mediaQuery.matches) 
    {
        // MATCH: Target page + Desktop screen size = Auto Expand immediately
        $rows.prop('open', true);
    } 
    else  // FALLBACK: Wrong page OR narrow viewport screen size = Initialize Closed
    {
        $rows.prop('open', false);
    }

    // 2. Listen for width changing AFTER load (Strictly one-way trigger when narrowing)
    mediaQuery.addEventListener('change', function(e)
    {
        if (e.matches) 
        {
            // Instantly collapses all rows only when viewport scales down past 668px
            $rows.prop('open', false);
        }
    });
    
	// --- 1. DEFINE THE INDIVIDUAL ROW TOGGLE LOGIC ---
	function handleRowToggle(event) 
	{
		if (this.open) 
		{    // Find the closest div wrapper and grab its ID
			const mid = $(this).closest('div');
			window.location.hash = '#' + mid.attr('id');
		//	mid.scrollIntoView({ behavior: 'smooth' });
		}
	}
	
	// Bind it initially
	$rows.on('toggle', handleRowToggle);

// 3. Master button manual control override (Instant)
	// --- 2. THE MASTER TOGGLE BUTTON ---
	$toggleBtn.on('click', function() 
	{
	  // Check the DOM: are ANY of the rows currently open?
		const anyOpen = $rows.filter(function() { return this.open; }).length > 0;
	  
	  // Temporarily remove the individual 'toggle' listener to block programmatic events
	 	$rows.off('toggle', handleRowToggle);

	  if (anyOpen)  // If at least one is open, close them all
		    $rows.prop('open', false);
	  else // If all are closed, open them all
			$rows.prop('open', true);
	
	  // Re-attach the listener so manual clicks still work
		$rows.on('toggle', handleRowToggle);
		window.location.hash = '#';
	});
});
</script>