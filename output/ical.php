<?php

/**
 * Helper function to generate an iCalendar-compliant VTIMEZONE block 
 * dynamically for any valid PHP timezone identifier.
 */
function gigpress_generate_vtimezone($tz_name) 
{
	try 
	{
		$tz = new DateTimeZone($tz_name);
	} 
	catch (Exception $e) 
	{
		// Fallback to absolute UTC if the timezone string is invalid
		return "BEGIN:VTIMEZONE\r\nTZID:UTC\r\nBEGIN:STANDARD\r\nDTSTART:19700101T000000\r\nTZOFFSETFROM:+0000\r\nTZOFFSETTO:+0000\r\nTZNAME:UTC\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n";
	}

	// Define a window tracking transitions from last year through 10 years out
	$current_year = intval(date('Y'));
	$start_time = mktime(0, 0, 0, 1, 1, $current_year - 1);
	$end_time = mktime(0, 0, 0, 1, 1, $current_year + 10);
	
	$transitions = $tz->getTransitions($start_time, $end_time);
	
	$output = "BEGIN:VTIMEZONE\r\n";
	$output .= "TZID:" . $tz_name . "\r\n";
	
	// Helper closure to format offsets into iCal format (e.g., -18000 -> -0500)
	$format_offset = function($seconds) 
	{
		$sign = ($seconds < 0) ? '-' : '+';
		$hours = floor(abs($seconds) / 3600);
		$mins = floor((abs($seconds) % 3600) / 60);
		return sprintf('%s%02d%02d', $sign, $hours, $mins);
	};

	if (empty($transitions)) 
	{
		// Handle timezones without active DST modifications (e.g., UTC, Asia/Kolkata)
		$offset = $tz->getOffset(new DateTime('now', $tz));
		$ical_offset = $format_offset($offset);
		
		$output .= "BEGIN:STANDARD\r\n";
		$output .= "DTSTART:19700101T000000\r\n";
		$output .= "TZOFFSETFROM:" . $ical_offset . "\r\n";
		$output .= "TZOFFSETTO:" . $ical_offset . "\r\n";
		$output .= "TZNAME:" . date_default_timezone_get() . "\r\n";
		$output .= "END:STANDARD\r\n";
	} 
	else 
	{
		// The first index of the array holds the baseline state at our $start_time boundary
		$pre_offset = $transitions[0]['offset'];
		
		// Loop through every actual structural adjustment point found
		for ($i = 1; $i < count($transitions); $i++) {
			$trans = $transitions[$i];
			$type = $trans['isdst'] ? 'DAYLIGHT' : 'STANDARD';
			
			// Calculate exact local time of transition using the prior offset rule
			$local_trans_time = $trans['ts'] + $pre_offset;
			$dtstart = gmdate('Ymd\THis', $local_trans_time);
			
			$offset_from = $format_offset($pre_offset);
			$offset_to = $format_offset($trans['offset']);
			
			$output .= "BEGIN:" . $type . "\r\n";
			$output .= "DTSTART:" . $dtstart . "\r\n";
			$output .= "TZOFFSETFROM:" . $offset_from . "\r\n";
			$output .= "TZOFFSETTO:" . $offset_to . "\r\n";
			$output .= "TZNAME:" . $trans['abbr'] . "\r\n";
			$output .= "END:" . $type . "\r\n";
			
			$pre_offset = $trans['offset'];
		}
	}
	
	$output .= "END:VTIMEZONE\r\n";
	return $output;
}

function gigpress_ical() 
{
	global $wpdb, $gpo;
	$further_where = '';
	if(isset($_GET['show_id']))
		$further_where .= $wpdb->prepare(' AND s.show_id = %d', $_GET['show_id']);

	if(isset($_GET['artist']))
		$further_where .= $wpdb->prepare(' AND s.show_artist_id = %d', $_GET['artist']);
	else if(isset($_GET['program_id']))
		$further_where .= $wpdb->prepare(' AND s.show_artist_id = %d', $_GET['program_id']);
	
	if(isset($_GET['tour'])) 
		$further_where .= $wpdb->prepare(' AND s.show_tour_id = %d', $_GET['tour']);
	
	if(isset($_GET['venue'])) 
		$further_where .= $wpdb->prepare(' AND s.show_venue_id = %d', $_GET['venue']);
	
	$limit = (!empty($gpo['rss_limit'])) ? $gpo['rss_limit'] : 100;
	
	// Automatically fallback to UTC if no timezone configuration is saved
	$tz = (!empty($gpo['timezone'])) ? $gpo['timezone'] : 'UTC';
	
	$shows = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM " . GIGPRESS_ARTISTS . " AS a, " . GIGPRESS_VENUES . " as v, " 
				. GIGPRESS_SHOWS ." AS s LEFT JOIN  " . GIGPRESS_TOURS . " AS t ON s.show_tour_id = t.tour_id " 			. "WHERE show_status != 'deleted' AND s.show_artist_id = a.artist_id AND s.show_venue_id = v.venue_id" 
				. $further_where . " AND s.show_expire >= '" . GIGPRESS_NOW 
				. "' ORDER BY s.show_date ASC, s.show_expire ASC, s.show_time ASC LIMIT %d", 
			$limit)
		);
	if($shows) 
	{
		$count = 1;
		$total = count($shows);
		foreach($shows as $show) 
		{
			$showdata = gigpress_prepare($show, 'ical');
			if(isset($_GET['artist'])) 
			{
				$filename = sanitize_title($showdata['artist_plain']) . '-icalendar';
				$title = $show->artist_name;
			} 
			elseif(isset($_GET['tour'])) 
			{
				$filename = sanitize_title($showdata['tour']) . '-icalendar';
				$title = $show->tour_name;
			} 
			elseif(isset($_GET['venue'])) 
			{
				$filename = sanitize_title($showdata['venue_plain']) . '-icalendar';
				$title = $show->venue_name;
			}
			elseif(isset($_GET['show_id'])) 
			{
				$filename = sanitize_title($showdata['artist_plain']) . '-' . $show->show_date;
				$title = $show->artist_name . ' - ' . $showdata['date'];
				$showdata['permalink'] .= "?show_id=" .  intval($_GET['show_id']);
			}
			else if(isset($_GET['program_id']))
			{
				$filename = sanitize_title($showdata['artist_plain']) . '-' . $show->show_date;
				$title = $show->artist_name . ' - ' . $showdata['date'];
				$showdata['permalink'] .= "?program_id=" .  intval($_GET['program_id']);
			}
			else 
			{
				$filename = sanitize_title(get_bloginfo('name')) . '-icalendar';
				$title = $gpo['rss_title'];
			}

			if($count == 1) 
			{
				header('Content-type: text/calendar');
				header('Content-Disposition: attachment; filename="' . $filename . '.ics"');	
				echo("BEGIN:VCALENDAR\r\n" . 
				"VERSION:2.0\r\n");
				if($total > 1) {
					echo("X-WR-CALNAME: $title\r\n");
				}
				echo("PRODID:GIGPRESS 2.0 WORDPRESS PLUGIN\r\n".
				"CALSCALE:GREGORIAN\r\n".
				"X-WR-TIMEZONE:" . $tz . "\r\n".
				"METHOD:PUBLISH\r\n");

				// Output the dynamic VTIMEZONE component contextually
				echo gigpress_generate_vtimezone($tz);
			}

			$local_start = $showdata['calendar_start'];
			$local_end   = $showdata['calendar_end'];

			// Convert backend UTC string back into a clean local footprint
			if (strlen($showdata['calendar_start']) > 8) 
			{
				$date_start_utc = DateTime::createFromFormat('Ymd\THis\Z', $showdata['calendar_start'], new DateTimeZone('UTC'));
				if ($date_start_utc) 
				{
					$date_start_utc->setTimezone(new DateTimeZone($tz));
					$local_start = $date_start_utc->format('Ymd\THis');
				}

				$date_end_utc = DateTime::createFromFormat('Ymd\THis\Z', $showdata['calendar_end'], new DateTimeZone('UTC'));
				if ($date_end_utc) 
				{
					$date_end_utc->setTimezone(new DateTimeZone($tz));
					$local_end = $date_end_utc->format('Ymd\THis');
				}
			}

			echo("BEGIN:VEVENT\r\n"
				. "SUMMARY:" . $showdata['calendar_summary_ical'] . "\r\n" 
				. "DESCRIPTION:" . $showdata['calendar_details_ical'] . "\r\n"
				. "LOCATION:" . $showdata['calendar_location_ical'] . "\r\n"
				. "UID:" . $local_start . '-' . $showdata['id'] . '-' . get_bloginfo('admin_email') . "\r\n"
				. "URL:" . $showdata['permalink'] . "\r\n");

			if(strlen($showdata['calendar_start']) == 8) 
			{
				echo("DTSTART;VALUE=DATE:" . $showdata['calendar_start'] . "\r\n" .
				       "DTEND;VALUE=DATE:" . $showdata['calendar_end'] . "\r\n");
			} 
			else 
			{
				echo("DTSTART;TZID=" . $tz . ":" . $local_start . "\r\n" . 
				       "DTEND;TZID=" . $tz . ":" . $local_end . "\r\n");
			}

			echo("DTSTAMP:" . date('Ymd') . "T" . date('his') . "Z\r\n"
				. "END:VEVENT\r\n");
			if($count == $total)
				echo("END:VCALENDAR");

			$count++;
		}
	} 
}
