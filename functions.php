<?php
namespace Kitbs;

if (stripos($_SERVER['PHP_SELF'], 'functions.php') !== false ) { http_response_code(404); die(); }

if (!file_exists('vendor/autoload.php')) {
	http_response_code(500);
	echo 'ERROR No vendor autoload file found. Run <code>composer install</code> first.';
	die();
}

include 'vendor/autoload.php';
use Sabre\VObject;

class Config {

	public $url;
	public $rename = array();
	public $include = array();
	public $exclude = array();

	private $fname = 'config.json';

	public function __construct() {
		if (!file_exists($this->fname)) {
			$this->save();
		}
		else {
			$this->load();
		}
	}

	public function save() {
		file_put_contents( $this->fname, json_encode($this) );
	}

	public function load() {
		$cfg = file_get_contents($this->fname);

		if ($cfg = json_decode($cfg)) {
			$this->url = $cfg->url;
			$this->exclude = (array) $cfg->exclude;
			$this->include = (array) $cfg->include;
			$this->rename = (array) $cfg->rename;
		}
	}

	public function excluded($event, $iftrue = true, $iffalse = false) {
		return in_array((string) $event->UID, $this->exclude) ? $iftrue : $iffalse;
	}
	public function renamed($event, $iftrue = true, $iffalse = false) {
		return isset($this->rename[(string) $event->UID]) ? $iftrue : $iffalse;
	}
	public function getRename($event, $iffalse = '') {
		return $this->renamed($event, @$this->rename[(string) $event->UID], $iffalse);
	}
}

class FbBirthdays {

	public $config;
	public $calendar;
	public $includecalendar;

	public function __construct() {
		$this->config = new \Kitbs\Config();
		$this-> savePost();
	}

	public function webcalToHttp($url) {
		return str_replace('webcal://', 'http://', $url);
	}

	public function savePost() {

		if (isset($_POST['save'])) {

			if (isset($_POST['url'])) {
				$this->config->url = $_POST['url'];
			}
			if (isset($_POST['exclude'])) {
				$this->config->exclude = $_POST['exclude'];
			}
			if (isset($_POST['rename']) && $this->config->url) {
				$rename = array();
				$renames = (array) $_POST['rename'];

				$this->loadCalendar();

				foreach ($this->calendar->VEVENT as $event) {
					if (@$renames[(string)$event->UID] != $this->justName($event->SUMMARY)) {
						$rename[(string) $event->UID] = @$renames[(string)$event->UID];
					}
				}

				$this->config->rename = $rename;
			}

			$include = array();

			if (isset($_FILES['icsfile'])) {
				$icsurl = @$_FILES['icsfile']["tmp_name"];

				if ($this->loadIncludeCalendar($icsurl)) {

					foreach ($this->includecalendar->VEVENT as $newdate) {
						$name = $this->justName($newdate->SUMMARY);
						$uid = md5($name);
						$dt = $newdate->DTSTART->getDateTime();

						if (!isset($_POST['include'][$uid])) {
							$include[$uid] = array(
								'uid' => $uid,
								'name' => $name,
								'month' => $dt->format('m'),
								'day' => $dt->format('d')
								);
						}
					}
				}
			}

			if (isset($_POST['include'])) {
				foreach ($_POST['include'] as $newdate) {
					if ($newdate['name']) {
						$newdate['uid'] = md5($newdate['name']);
						$include[$newdate['uid']] = $newdate;
					}
				}
			}

			usort($include, array($this, 'newDateSort'));	
			$this->config->include = $include;

			$this->config->save();
			header( 'Location: '.$_SERVER['PHP_SELF'].'#!'.$this->thisTab() );
			die();
		}
	}

	public function thisTab() {
		if (isset($_POST['save'])) {
			$tabs = array_keys($_POST['save']);
			return array_pop($tabs);
		}
	}

	public function getCacheFile($file) {
		return 'cache/'.md5($file);
	}

	public function loadCalendar() {
		if ($this->config->url) {

			$cache = $this->getCacheFile($this->config->url);

			if (file_exists($cache)) {
				$data = file_get_contents($cache);
			}
			else {
				$data = file_get_contents($this->config->url);
				file_put_contents($cache, $data);
			}
			try {
				$this->calendar = VObject\Reader::read($data);
				return true;
			} catch (Exception $e) {}
		}
	}

	public function loadIncludeCalendar($includeurl=false) {
		if (file_exists($includeurl)) {
			$data = file_get_contents($includeurl);

			try {
				$this->includecalendar = VObject\Reader::read($data);
				return true;
			} catch (Exception $e) {}

		}
	}

	public function loadFilteredCalendar() {

		$cache = $this->getCacheFile(serialize($this->config));

		if (file_exists($cache)) {
			return file_get_contents($cache);
		}

	}
	public function saveFilteredCalendar() {

		$cache = $this->getCacheFile(serialize($this->config));
		file_put_contents($cache, $this->calendar->serialize());

	}

	public function newDateSort($a, $b) {
		$amd = ($a['month'] * 100) + $a['day'];
		$bmd = ($b['month'] * 100) + $b['day'];

		if ($amd == $bmd) {
			return 0;
		}
		return ($amd > $bmd) ? +1 : -1;
	}

	public function justName($name) {
		return str_replace('\'s Birthday', '', $name);
	}

	public function monthPicker($id, $month = false) {
		echo '<select name="include['. $id .'][month]" class="form-control bmonth">
		<option></option>';
		for ($i=1; $i <= 12; $i++) { 
			$sel = $i == $month ? ' selected' : false;
			echo '<option value="'. $i .'"'.$sel.'>'. date("F", mktime(0, 0, 0, $i, 10)) .'</option>'.PHP_EOL;
		}
		echo '</select>';
	}
	public function dayPicker($id, $day = false, $month = false) {
		echo '<select name="include['.$id.'][day]" class="form-control bday">
		<option></option>';

		switch ($month) {
			case 2:
			$days = 29;
			break;
			case 4:
			case 6:
			case 9:
			case 11:
			$days = 30;
			break;
			default:
			$days = 31;
		}

		for ($i=1; $i <= $days; $i++) {
			$sel = $i == $day ? ' selected' : false;
			$cls = $i > 29 ? ' class="sel'.$i.'"' : false;
			echo '<option value="'. $i .'"'.$sel.$cls.'>'. $i .'</option>'.PHP_EOL;
		}

		echo '</select>';
	}
	public function includeName($id, $name = false) {
		$disabled = $name ?: ' disabled';
		echo '<div class="input-group">
		<input type="text" name="include['.$id.'][name]" class="form-control includenamebox" placeholder="Name" value="'.$name.'" />
		<span class="input-group-btn">
		<button class="btn btn-default deleteinclude" type="button"'.$disabled.'>Delete</button>
		</span>
		</div>';
	}


}
