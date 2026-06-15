
<button id="toggle-all-cast" class="button">Open/Close All Bios</button>

<script>
jQuery(document).ready(function($) 
{
    let masterOpenState = true; // Independent master state tracker
    
    $('#toggle-all-cast').on('click', function() 
    {
        masterOpenState = !masterOpenState; // Flip the state
        
        // Force all elements to match the master state uniformly
        $('details.cast-member-row').prop('open', masterOpenState);
    });
});
</script>
