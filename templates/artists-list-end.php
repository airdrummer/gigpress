<!-- begin gigpress artist-list-end -->

<?php

	if( $atts['program_id'] or $atts['genres'] 
	    or !empty($srchstrgs) || !empty($selected_genres))
	{
		echo "<style type='text/css'>.hero-image { display: none; } </style>";
		echo "<div class='embed-viewall artist-list-end' ><a href='" . get_permalink() . "'" 
                    . " title='display all programs'"
                    . "   alt='display all programs'"
		            . " class='viewall button'>view all programs</a></div>";
	}
	
	include GIGPRESS_PLUGIN_DIR . '/scripts/showInfo-js.html';

?>
<!-- end gigpress artist-list-end -->
