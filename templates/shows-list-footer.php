<!--begin gigpress show list footer -->
<?php	
// This template is displayed at the very end of our shows listing.
// By default, it displays links to RSS and iCal feeds for all upcoming shows,
// or, if we're filtering for a specific program_id or tour, just for that specific program_id or tour

    if( $program_id )
	{
		echo "<div class='viewall ctr'>view this program's ";
		if ($program_id && $show_id) 
		{
		    echo "<a href='/about/company-collaborators/"
    	        . ($scope == 'past'
    	            ? "this-seasons-casts" 
    	            : 'past-seasons-casts')
		        . "/?program_id=" . $program_id . "'>"
		        . "past casts</a>" ;
		}

		if ( ($program_id && $show_id) || ($scope !== 'past'))
    	    echo " <a href='/performances/past-performances/"
    	            . "?program_id=" . $program_id . "'>"
    	            . " past shows</a>";
    
		if ( ($program_id && $show_id) || ($scope == 'past'))
    	    echo " <a href='/performances/"
    	            . "?program_id=" . $program_id . "'>"
    	            . " upcoming shows</a>";

		echo "</div><br>&nbsp";
	}

    if(!empty($gpo['display_subscriptions'])) : ?>
	<p class="gigpress-subscribe"><?php _e("Subscribe", "gigpress"); ?>: 
	
	<?php if(!$program_id && !$tour && !$venue) : ?>
		<a href="<?php echo GIGPRESS_RSS; ?>" title="<?php echo wptexturize($gpo['rss_title']); ?> RSS" class="gigpress-rss">RSS</a> | <a href="<?php echo GIGPRESS_WEBCAL; ?>" title="<?php echo wptexturize($gpo['rss_title']); ?> iCalendar" class="gigpress-ical">iCal</a>
	<?php endif; ?>

	<?php if($program_id) : ?>
		<a href="<?php echo GIGPRESS_RSS; ?>&amp;program_id=<?php echo $showdata['artist_id']; ?>" title="<?php echo $showdata['artist_plain']; ?> RSS" class="gigpress-rss">RSS</a> | <a href="<?php echo GIGPRESS_WEBCAL; ?>&amp;program_id=<?php echo $showdata['artist_id']; ?>" title="<?php echo $showdata['artist_plain']; ?> iCalendar" class="gigpress-ical">iCal</a>
	<?php endif; ?>	
		
	<?php if($tour) : ?>
		<a href="<?php echo GIGPRESS_RSS; ?>&amp;tour=<?php echo $showdata['tour_id']; ?>" title="<?php echo $showdata['tour']; ?> RSS" class="gigpress-rss">RSS</a> | <a href="<?php echo GIGPRESS_WEBCAL . '&amp;tour=' . $showdata['tour_id']; ?>" title="<?php echo $showdata['tour']; ?> iCalendar" class="gigpress-ical">iCal</a>
	<?php endif; ?>	

	<?php if($venue) : ?>
		<a href="<?php echo GIGPRESS_RSS; ?>&amp;venue=<?php echo $showdata['venue_id']; ?>" title="<?php echo $showdata['venue_plain']; ?> RSS" class="gigpress-rss">RSS</a> | <a href="<?php echo GIGPRESS_WEBCAL . '&amp;venue=' . $showdata['venue_id']; ?>" title="<?php echo $showdata['venue_plain']; ?> iCalendar" class="gigpress-ical">iCal</a>
	<?php endif; ?>	
		<br><hr>
	</div>				
<?php endif; ?>
<!--end gigpress show list footer -->
