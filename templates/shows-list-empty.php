<!-- begin gigpress shows-list-empty-->
<?php
	
// This template displays when you have no shows.

	 echo '<div class="gigpress-empty">';
	 
	 $program_name = '';
	 $no_results_message = ($scope == 'upcoming' 
							? $gpo['noupcoming']
							: ($scope == 'past'
								? $gpo['nopast']
								: 'No performances'
									. ($scope == 'today' ? ' today'	: '')))
						 . (isset($dateRange) ? $dateRange : "");							 
	 $no_results_message = wptexturize($no_results_message);
	 
	 if ($program_id)
	 {
		$programs = $wpdb->get_results("SELECT * FROM " . GIGPRESS_ARTISTS
	 			.  ' where artist_id = ' . $wpdb->prepare('%d', $program_id) );

	 	if ($programs)
	 	{
	 		foreach($programs as $program)
		 	{
		 		$program_name = $program->artist_name;
		 		//$program_id   = $program->artist_id;
		 		echo $no_results_message . wptexturize(" for ") 
					 . "<h2 class=progtitle >" . wptexturize($program_name) . "</h2>";

            	echo "<div class=embed-viewall>";
            	
				    echo "<a href='/programs-repertoire/?program_id=" . $program_id . "'"
				                . " title='view program description'"
                    			. "   alt='view program description'" . " >"
                            . '<button class=viewall>view program description</button>'
					    . "</a>";

		 		if ($scope != 'all')
		 		{
		 			if (($scope != 'upcoming') or isset($dateRange) )
		 				echo "<a href=/performances/?program_id=" . $program_id . "'"
				                . " title='view upcoming performances of this program'"
                    			. "   alt='view upcoming performances of this program'" . " >"
                                . '<button class=viewall>view upcoming performances</button>'
		 				   .  "</a>";
		 			if ($scope != 'past')
			 			echo "<a href=/performances/past-performances?program_id=" . $program_id . "'"
				                . " title='view past performances of this program'"
                    			. "   alt='view past performances of this program'" . " >"
                                . '<button class=viewall>view past performances</button>'
		 				   .  "</a>";
		 		}
		 		echo "</div>";
		 		$program_name = "?title=" . urlencode($program_name); // for /pastPerfs.html single program
		 	}
	 	}
	 	else
	 		echo "<span class=error>invalid program id: " . $program_id . "</span>";
	 }
	 else
		 echo $no_results_message;
?>
</div>
<hr>
<!-- end gigpress shows-list-empty-->
