<!--gigpress artist/program-list-start -->
<?php

	if( ! $program_id )
	{
		echo "<p class='gamma gig-pup set-order' ><a href='/programs-repertoire/?artist_order=";
		if ( $artist_order == 'alpha')
			echo "custom'>list in preferred order";
		else
			echo "alpha'>list in alphabetical order";
		echo "</a></p>";
	}
?>
<!--gigpress list-start end -->