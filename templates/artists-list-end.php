<?php
//	bostoncamerata.org
//--gigpress prog-list-end start

	if( $atts['program_id'] or $atts['genres'] 
		or !empty($srchstrgs) || !empty($selected_genres))
	{
		echo "<style type='text/css'>.hero-image { display: none; } </style>";
	 	echo "<h4><a class=floatright href='" . get_permalink() . "'>view all programs</a></h4>";
	}
?>

<div class=hide>
</div><!--gigpress list-end end -->