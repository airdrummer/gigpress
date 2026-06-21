<!-- begin gigpress artist-list -->

	<div class="gigpress-artist" id="program-<?php echo $showdata['artist_id']; ?>" >
		<a title='click to show/hide program description'
			href="#program-<?php echo $showdata['artist_id']; ?>"
			onclick="return showInfo('prog-note-<?php echo $showdata['artist_id']; ?>')" >
			<h2 class="progtitle" >
			    <?php echo bc_bankhead($showdata['artist']); ?>
		</h2></a>
<?php
		if(!empty($showdata['program_notes']))
		{
			echo '<div class="prog-note" id="prog-note-' . $showdata['artist_id'] . '" > <!-- start prog-note -->';
			echo $showdata['program_notes'];
			if(!empty($gpo['artist_link'])
		  	&& !empty($showdata['artist_url'])
		  	&& (strpos($showdata['artist_url'],"#program-" . $showdata['artist_id']) === false))
				echo '&nbsp;&nbsp;&nbsp;<a class="more-info" href="' . esc_url($showdata['artist_url']) . '"'
                        . gigpress_target($showdata['artist_url']) . '>read more...</a>';
			echo '</div>';
		}

		if(!empty($showdata['genres']))
			echo "<div class='floatright prog-genres'>" . $showdata['genres'] . "</div><br>";

		echo '<div class="embed-viewall">';	
    		echo '<a href="/performances/?condensed=1&program_id=' . $showdata['artist_id']
				  . '" class=viewall >Upcoming&nbsp;Performances</a>';
    		echo '<a href="/performances/past-performances/?condensed=1&program_id=' . $showdata['artist_id']
				  . '" class=viewall >Past&nbsp;Performances</a>';
        echo "</div>";

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
?>
<br clear=both>
	</div>
<!-- end gigpress artist-list -->
