<!-- 
	include GIGPRESS_PLUGIN_DIR . '/scripts/showInfo.js';
  -->
  
<script type="text/javascript">
	var currentInfo = null;
	function showInfo(id)
	{
		if(currentInfo != null)
			currentInfo.style.display = "none";

		if(id != null)
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
	
	// 1. Target ONLY the 5 explicit .progtitle elements
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
	            parentEvent.prepend(img);
	    }
	});

</script>