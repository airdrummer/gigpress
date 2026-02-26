<?php
	echo '<hr clear=left style="margin-top:2em;">';
	echo '<div class="gigpress-artist" id="program-' . $showdata['artist_id']
		     . '"><h2 class=progtitle><a href="/programs-repertoire/?program_id='
			 . $showdata['artist_id'] . '" title="open program description">'
		      . bc_bankhead($showdata['artist'])
		       . '</a></h2>';

		if(!empty($showdata['program_notes'])) : ?>
			<div class="prog-note" id="prognote-<?php echo $showdata['artist_id']; ?>"> <!-- start prog-note -->
			<?php 
				echo $showdata['program_notes'];

				if(!empty($showdata['genres']))
					echo "<div class=prog-genres>Genres: "
							. implode(', ', $showdata['genres'])
						. "</div>"; 
				
				if(!empty($gpo['artist_link'])
				  && !empty($showdata['artist_url'])
				  && (strpos($showdata['artist_url'],"#program-" . $showdata['artist_id']) === false))
		          {
					echo '&nbsp;&nbsp;&nbsp;<a class="more-info" href="' . esc_url($showdata['artist_url']) . '"'
						 . gigpress_target($showdata['artist_url']) . '>read more...</a>';
						} ?>
			</div>
	<?php endif;
	
		echo '<div class="more-info info-right">';
		
		echo 	'<a href="/performances/?program_id='
				  . $showdata['artist_id'] . '"><h3 class=gig-pup>Upcoming&nbsp;Performances</h3></a> '
				  . '&nbsp;&nbsp;&nbsp; <a href="/performances/past-performances/?program_id='
				  . $showdata['artist_id'] . '"><h3 class=gig-pup>Past&nbsp;Performances</h3></a>';

		if(!empty($gpo['display_subscriptions']))
		{
			echo '<div class="info-right">';
			echo ' <a href="'. GIGPRESS_RSS . '&amp;program_id=' . $showdata['artist_id']
				 . '" alt="subscribe to RSS feed" title="subscribe to RSS feed">'
				 . '<img src="' . plugins_url('/gigpress/images/feed-icon-12x12.png') . '" /></a>'
		    . '&nbsp;<a href="' . GIGPRESS_WEBCAL . '&amp;program_id=' . $showdata['artist_id'] 
				 . '" alt="subscribe to iCalendar" title="subscribe to iCalendar">'
			     . '<img src="'. plugins_url('/gigpress/images/icalendar-icon.gif') . '" /></a>';
			echo '</div>';
		}
		echo '</div>';

?>
<br clear=both>
	</div><!-- end prog-list -->
