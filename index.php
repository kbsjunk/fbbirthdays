<?php

if (!file_exists( 'config.php' )) {
	http_response_code(500);
	echo 'ERROR No config file defined. Go to <a href="admin.php">Admin</a> to set up.';
	die();
}

include 'config.php';
include 'functions.php';

if ($calendar = getfilteredcalendar(array('url'=>$url, 'exclude'=>$exclude, 'include'=>$include))) {
//NOTHING :-)
}
else {
	$calendar = getcalendar($url);
	$birthdays = array();

	foreach($calendar->VEVENT as $event) {
		if (!in_array($event->UID, $exclude)) {
			$birthdays[] = $event;
		}
	}

	$calendar->remove('VEVENT');
	$calendar->VEVENT = $birthdays;
	//$calendar->VEVENT = array();//$birthdays;

	$calendar = $calendar->serialize();
	savefilteredcalendar($calendar, array('url'=>$url, 'exclude'=>$exclude, 'include'=>$include));
}
http_response_code(200);
header('Content-type: text/plain; charset=utf-8');
//header('Content-type: text/calendar; charset=utf-8');
//header('Content-Disposition: attachment; filename=birthdays.ics');

echo $calendar;
die();