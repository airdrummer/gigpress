<!-- begin gigpress artist-list-end -->

<?php
//	bostoncamerata.org
//--gigpress prog-list-end start

	if( $atts['program_id'] or $atts['genres'] 
	    or !empty($srchstrgs) || !empty($selected_genres))
	{
		echo "<style type='text/css'>.hero-image { display: none; } </style>";
		echo "<div class=embed-viewall><a href='" . get_permalink() . "'>"
                . '<button class=viewall>view all programs</button>'
            . "</a></div>";
	}
?>
<!-- end gigpress artist-list-end -->
