<?php

if (!file_exists( 'config.json' )) {
	http_response_code(500);
	echo 'ERROR No config file defined. Go to <a href="admin.php">Admin</a> to set up.';
	die();
}

include 'functions.php';
$fb = new \Kitbs\FbBirthdays();

if ($calendar = $fb->loadFilteredCalendar()) {
// NOTHING :-)
}
elseif ($fb->loadCalendar()) {
	$birthdays = array();

	foreach($fb->calendar->VEVENT as $event) {
		if (!$fb->config->excluded($event)) {
			
			$name = $fb->justName($event->SUMMARY);

			if ($fb->config->renamed($event)) {
				$name = $fb->config->getRename($event);
			}

			$event->remove('SUMMARY');
			$event->SUMMARY = $name;
			
			$birthdays[] = $event;
		}
	}

	$fb->calendar->remove('VEVENT');
	
	foreach ($birthdays as $newdate) {
		$fb->calendar->add('VEVENT',  array(
			'UID' => $newdate->UID,
			'SUMMARY' =>  $newdate->SUMMARY,
			'DTSTART' =>  $newdate->DTSTART,
			'RRULE' =>  $newdate->RRULE,
			'DURATION' =>  $newdate->DURATION,
			));
	}

	foreach ($fb->config->include as $uid => $newdate) {
		$now = new \DateTime();

		$month = $newdate->month;
		$day = $newdate->day;
		$year = $now->format('Y');

		$error = false;

		try {
			$dt = new \DateTime("$year-$month-$day");
		} catch (Exception $e) {
			$error = true;
		}

		$leap = false;

		if ($month == 2 && $day == 29 && $dt->format('m') == 3) {
			$leap = ' (29 Feb)';
		}

		if (!$error) {
			if ($dt < $now) {
				$dt->add(new DateInterval('P1Y'));
			}

			$fb->calendar->add('VEVENT', array(
				'UID' => $uid.'@custom',
				'SUMMARY' => $newdate->name.$leap,
				'DTSTART' => $dt->format('Ymd'),
				'RRULE' => 'FREQ=YEARLY',
				'DURATION' => 'P1D'
				)
			);
		}
	}

	$fb->saveFilteredCalendar();
	$calendar = $fb->calendar->serialize();
}
else {
	//http_response_code(500);
	//die();
}
http_response_code(200);
header('Content-type: text/plain; charset=utf-8');
//header('Content-type: text/calendar; charset=utf-8');
//header('Content-Disposition: attachment; filename=birthdays.ics');

echo $calendar;
die();