<!-- 
	include GIGPRESS_PLUGIN_DIR . '/scripts/showInfo.js';
  -->

<script type="text/javascript">
	var currentInfo = null;
	function showInfo(id)
	{
		if(currentInfo !== null)
			currentInfo.style.display = "none";

		if(id !== null)
		{
			ci = document.getElementById(id);
			if(currentInfo == ci)
				currentInfo = null;
			else 
			{
				currentInfo = ci;
				currentInfo.style.display = "block";
			}
			return true;
		}
		return false;
	}

    // 1. Target ONLY .progtitle elements
    document.querySelectorAll('.progtitle').forEach(titleElement => 
    {
        // 2. Find the closest parent event container for this specific title
        const parentEvent = titleElement.closest('div.event');
        
        if (parentEvent) 
        {
            // 3. Find the image that belongs ONLY to this specific event container
            const img = parentEvent.querySelector('.prog-note img:first-of-type');
            
            // 4. Move it to the top of this event container
	        if (img)
	        {
		        const prefix = 'size-';    
                // Convert classList to an array and loop through it
                [...img.classList].forEach(className => 
                {
                    if (className.startsWith(prefix)) 
                        img.classList.remove(className);
                });
                const titleBlock = parentEvent.querySelector('.title-block');
	            titleBlock.prepend(img);
	        }
        }
    });
    
    document.querySelectorAll('a.gigpress-tickets-link').forEach(tixLink => 
    {
        tixLink.title = 'click to purchase tickets for this performance';
    });

</script>