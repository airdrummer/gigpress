<?php

/**
 * Helper function to mostly determine standard PHP Timezone Identifiers 
 * dynamically based on GigPress venue location fields.
 */
function gigpress_get_timezone_by_location($default_tz, $country, $state, $city = '') 
{
	$country = strtoupper(trim($country));
	$state   = strtoupper(trim($state));
	$city    = strtolower(trim($city));

	// If country is US, USA, or left blank, determine zone by State code
	if ($country === 'US' || $country === 'USA' || $country === 'UNITED STATES' || empty($country)) 
	{
		// Pacific Time Zone
		if (in_array($state, ['CA', 'OR', 'WA', 'NV'])) 
			return 'America/Los_Angeles';
		
		// Mountain Time Zone
		if (in_array($state, ['CO', 'UT', 'NM', 'WY', 'MT', 'ID'])) 
			return 'America/Denver';

		if ($state === 'AK')
			return 'America/Anchorage';
			
		if ($state === 'HA')
			return 'Pacific/Honolulu';
			
		if ($state === 'AZ')
			return 'America/Phoenix';

		// Central Time Zone
		if (in_array($state, ['IL', 'TX', 'MN', 'MO', 'WI', 'TN', 'LA', 
								'ND', 'SD', // eastern parts
		                      'OK', 'KS', 'NE', 'IA', 'AR', 'MS', 'AL']))
			return 'America/Chicago';

		// Eastern Time Zone (Default fallback for New England / New York events)
		return 'America/New_York';
	}

	// International Handlers for Tour Destinations
	switch ($country) 
	{
		// Japan
		case 'JP':
		case 'JAPAN':
			return 'Asia/Tokyo';

		// Nordic Countries
		case 'NO':
		case 'NORWAY':
			return 'Europe/Oslo';
		case 'SE':
		case 'SWEDEN':
			return 'Europe/Stockholm';
		case 'DK':
		case 'DENMARK':
			return 'Europe/Copenhagen';
		case 'FI':
		case 'FINLAND':
			return 'Europe/Helsinki';
		case 'IS':
		case 'ICELAND':
			return 'Atlantic/Reykjavik';

		// Western & Central Europe
		case 'FR':
		case 'FRANCE':
			return 'Europe/Paris';
		case 'DE':
		case 'GERMANY':
			return 'Europe/Berlin';
		case 'GB':
		case 'UK':
		case 'UNITED KINGDOM':
		case 'ENGLAND':
			return 'Europe/London';
		case 'NL':
		case 'NETHERLANDS':
			return 'Europe/Amsterdam';
		case 'BE':
		case 'BELGIUM':
			return 'Europe/Brussels';
		case 'IT':
		case 'ITALY':
			return 'Europe/Rome';
		case 'ES':
		case 'SPAIN':
			return 'Europe/Madrid';
		
		// Canada
		case 'CA':
		case 'CANADA':
			if (in_array($state, ['QC', 'ON', 'NB', 'NS'])) 
				return 'America/Toronto';
			if ($state === 'BC') 
				return 'America/Vancouver';
			if ($state === 'AB') 
				return 'America/Edmonton';
			return 'America/Toronto';
	}

	// Ultimate structural fallback if location remains unrecognizable
	return $default_tz;
}

/**
 * Generates fully compliant VTIMEZONE structural definitions 
 * via PHP's local compiled zoneinfo transition records.
 */
function gigpress_generate_vtimezone($tz_name) 
{
	try {
		$tz = new DateTimeZone($tz_name);
	} catch (Exception $e) {
		return "BEGIN:VTIMEZONE\r\nTZID:UTC\r\nBEGIN:STANDARD\r\nDTSTART:19700101T000000\r\nTZOFFSETFROM:+0000\r\nTZOFFSETTO:+0000\r\nTZNAME:UTC\r\nEND:STANDARD\r\nEND:VTIMEZONE\r\n";
	}

	$current_year = intval(date('Y'));
	$start_time = mktime(0, 0, 0, 1, 1, $current_year - 1);
	$end_time = mktime(0, 0, 0, 1, 1, $current_year + 10);
	$transitions = $tz->getTransitions($start_time, $end_time);
	
	$output = "BEGIN:VTIMEZONE\r\nTZID:" . $tz_name . "\r\n";
	
	$format_offset = function($seconds) 
		{
			$sign = ($seconds < 0) ? '-' : '+';
			$hours = floor(abs($seconds) / 3600);
			$mins = floor((abs($seconds) % 3600) / 60);
			return sprintf('%s%02d%02d', $sign, $hours, $mins);
		};

	if (empty($transitions)) 
	{
		$offset = $tz->getOffset(new DateTime('now', $tz));
		$ical_offset = $format_offset($offset);
		$output .= "BEGIN:STANDARD\r\nDTSTART:19700101T000000\r\nTZOFFSETFROM:" . $ical_offset . "\r\nTZOFFSETTO:" . $ical_offset . "\r\nTZNAME:STD\r\nEND:STANDARD\r\n";
	} else {
		$pre_offset = $transitions[0]['offset'];
		for ($i = 1; $i < count($transitions); $i++) {
			$trans = $transitions[$i];
			$type = $trans['isdst'] ? 'DAYLIGHT' : 'STANDARD';
			$local_trans_time = $trans['ts'] + $pre_offset;
			$dtstart = gmdate('Ymd\THis', $local_trans_time);
			
			$output .= "BEGIN:" . $type . "\r\n";
			$output .= "DTSTART:" . $dtstart . "\r\n";
			$output .= "TZOFFSETFROM:" . $format_offset($pre_offset) . "\r\n";
			$output .= "TZOFFSETTO:" . $format_offset($trans['offset']) . "\r\n";
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
	
	$default_tz = (!empty($gpo['timezone'])) ? $gpo['timezone'] : 'America/New_York';
	
	$shows = $wpdb->get_results(
			$wpdb->prepare("SELECT * FROM " . GIGPRESS_ARTISTS . " AS a, " . GIGPRESS_VENUES . " as v, " 
			. GIGPRESS_SHOWS ." AS s LEFT JOIN  " . GIGPRESS_TOURS . " AS t ON s.show_tour_id = t.tour_id"
			. " WHERE show_status != 'deleted' AND s.show_artist_id = a.artist_id AND s.show_venue_id = v.venue_id" 
			. $further_where . " AND s.show_expire >= '" . GIGPRESS_NOW 
			. "' ORDER BY s.show_date ASC, s.show_expire ASC, s.show_time ASC LIMIT %d", 
			$limit)
		);

	if($shows) 
	{
		// 1. Scan results to attach dynamic zone calculations & pull unique zones
		$unique_timezones = [];
		foreach($shows as $key => $show) 
		{
			$computed_tz = gigpress_get_timezone_by_location(
								$default_tz,
								$show->venue_country ?: '',
								$show->venue_state ?: '',
								$show->venue_city ?: '' );
			$shows[$key]->computed_tz = $computed_tz;
			
			if (!in_array($computed_tz, $unique_timezones)) 
				$unique_timezones[] = $computed_tz;
		}

		$count = 1;
		$total = count($shows);
		foreach($shows as $show) 
		{
			$showdata = gigpress_prepare($show, 'ical');
			
			if(isset($_GET['show_id'])) 
			{
				$filename = sanitize_title($showdata['artist_plain']) . '-' . $show->show_date;
				$title = $show->artist_name . ' - ' . $showdata['date'];
			}
			elseif(isset($_GET['program_id']))
			{
				$filename = sanitize_title($showdata['artist_plain']) . '-' . $show->show_date;
				$title = $show->artist_name . ' - ' . $showdata['date'];
			}
			elseif(isset($_GET['artist'])) 
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
			else
			{
				$filename = sanitize_title(get_bloginfo('name')) . '-icalendar';
				$title = $gpo['rss_title'];
			}

			if($count == 1) 
			{
				header('Content-type: text/calendar');
				header('Content-Disposition: attachment; filename="' . $filename . '.ics"');	
				echo("BEGIN:VCALENDAR\r\n"  
					. "VERSION:2.0\r\n");
				if($total > 1)
					echo("X-WR-CALNAME: $title\r\n");

				echo("PRODID:GIGPRESS 2.0 WORDPRESS PLUGIN\r\n"
					. "CALSCALE:GREGORIAN\r\n"
					. "X-WR-TIMEZONE:" . $default_tz . "\r\n"
					. "METHOD:PUBLISH\r\n");

				// Output VTIMEZONE blocks for every unique zone used in this feed file
				foreach ($unique_timezones as $utz)
					echo gigpress_generate_vtimezone($utz);
			}

			// Capture the precise timezone calculated for this specific venue
			$tz = $show->computed_tz;

			$local_start = $showdata['calendar_start'];
			$local_end   = $showdata['calendar_end'];

			// Convert backend UTC string into the venue's targeted local timestamp
			if (strlen($showdata['calendar_start']) > 8) // not all-day
			{
				$date_start_utc = DateTime::createFromFormat('Ymd\THis\Z', 
															 $showdata['calendar_start'], 
															 new DateTimeZone('UTC'));
				if ($date_start_utc) 
				{
					$date_start_utc->setTimezone(new DateTimeZone($tz));
					$local_start = $date_start_utc->format('Ymd\THis');
				}

				$date_end_utc = DateTime::createFromFormat('Ymd\THis\Z', 
															$showdata['calendar_end'], 
															new DateTimeZone('UTC'));
				if ($date_end_utc) 
				{
					$date_end_utc->setTimezone(new DateTimeZone($tz));
					$local_end = $date_end_utc->format('Ymd\THis');
				}
			}

			echo( "BEGIN:VEVENT\r\n"  
				. "SUMMARY:"     . $showdata['calendar_summary_ical']  . "\r\n" 
				. "DESCRIPTION:" . $showdata['calendar_details_ical']  . "\r\n"
				. "LOCATION:"    . $showdata['calendar_location_ical'] . "\r\n" 
				. "UID:" . $local_start . '-' . $showdata['id'] . '-' 
						 				. get_bloginfo('admin_email')  . "\r\n"
				. "URL:" . $showdata['permalink'] 
						 			. "?show_id=" . $showdata['id']    . "\r\n" );

			if(strlen($showdata['calendar_start']) == 8) // all-day
				echo("DTSTART;VALUE=DATE:" . $local_start . "\r\n" 
				     . "DTEND;VALUE=DATE:" . $local_end . "\r\n" );

			else  // Pair the event directly to its venue-specific structural target zone
				echo("DTSTART;TZID=" . $tz . ":" . $local_start . "\r\n" 
				     . "DTEND;TZID=" . $tz . ":" . $local_end . "\r\n" );

			echo( "DTSTAMP:" . date('Ymd') . "T" . date('his') . "Z\r\n"
				. "END:VEVENT\r\n" );

			if($count == $total) 
				echo("END:VCALENDAR" );

			$count++;
		}
	} 
}