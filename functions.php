<?php
if (stripos($_SERVER['PHP_SELF'], 'functions.php') !== false ) { http_response_code(404); die(); }

if (!file_exists('vendor/autoload.php')) {
	http_response_code(500);
	echo 'ERROR No vendor autoload file found. Run <code>composer install</code> first.';
	die();
}

include 'vendor/autoload.php';
use Sabre\VObject;

function saveconfig( $url = false, $exclude = false, $include = false, $rename = false ) {
	if (is_array($exclude)) {
		$exclude = '\''.implode( '\',
			\'', $exclude ).'\'';
	}
	if (is_array($include)) {
		$include = '\''.implode( '\',
			\'', $include ).'\'';
	}
	if (is_array($rename)) {
		foreach ($rename as $key => &$value) {
			$value = '\''.$key.'\' => \''.addslashes($value).'\'';
		}
		$rename = implode( ',
			', $rename );
	}
	

	if ($url) {
		$url = webcalToHttp($url);
	}

	$baseconfig = '<?php
	if (stripos($_SERVER[\'PHP_SELF\'], \'config.php\') !== false ) { http_response_code(404); die(); }

	$url = \''.$url.'\';

	$rename = array('.$rename.');

	$include = array('.$include.');

	$exclude = array('.$exclude.');';

	file_put_contents( 'config.php', $baseconfig );
}

function getcalendar($url) {

	$cache = 'cache/'.md5($url);

	if (file_exists($cache)) {
		$data = file_get_contents($cache);
	}
	else {
		$data = file_get_contents($url);
		file_put_contents($cache, $data);
	}

	return VObject\Reader::read($data);
}

function getfilteredcalendar($config) {

	$cache = 'cache/'.md5(serialize($config));

	if (file_exists($cache)) {
		return file_get_contents($cache);
	}
}
function savefilteredcalendar($calendar, $config) {
	$cache = 'cache/'.md5(serialize($config));
	file_put_contents($cache, $calendar);
}

function justname($name) {
	return str_replace('\'s Birthday', '', $name);
}
function webcalToHttp($url) {
	return str_replace('webcal://', 'http://', $url);
}

function monthPicker($id, $month = false) {
	echo '<select name="include['. $id .'][month]" class="form-control bmonth">
	<option></option>';
	for ($i=1; $i <= 12; $i++) { 
		$sel = $i === $month ? ' selected="selected"' : false;
		echo '<option value="'. $i .'"'.$sel.'>'. $i .'</option>'.PHP_EOL;
	}
	echo '</select>';
}
function dayPicker($i, $day = false) {
	echo '<select name="include['.$i.'][day]" class="form-control bday">
	<option></option>';
	for ($i=1; $i <= 31; $i++) { 
		$sel = $i === $day ? ' selected="selected"' : false;
		$cls = $i >= 29 ? ' class="sel'.$i.'"' : false;
		echo '<option value="'. $i .'"'.$sel.$cls.'>'. $i .'</option>'.PHP_EOL;
	}
	
	echo '</select>';
}
function includeName($i, $name = false) {
	echo '<input type="text" name="include['.$i.'][name]" class="form-control" placeholder="Name" value="'.$name.'" />';
}