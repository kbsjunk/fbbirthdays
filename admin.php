<!DOCTYPE html>
<?php

$url = '';
$exclude = '';
$include = '';
$newconfig = false;

// -------------------------------------------- //

include 'functions.php';

if (!file_exists( 'config.php' )) {
	saveconfig();
	$newconfig = true;
}

include 'config.php';

// -------------------------------------------- //

if (isset($_POST['url'])) {
	$url = $_POST['url'];
}
if (isset($_POST['exclude'])) {
	$exclude = $_POST['exclude'];
}
if (isset($_POST['saveevents'])) {
	saveconfig($url, $exclude);
	header( 'Location: '.$_SERVER['PHP_SELF'] );
	die();
}

?>
<html lang="en">
<head>
	<title>Facebook Birthday Calendar Filter</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">
	<style type="text/css">
	.list-group-item .checkbox { margin:0; }
	</style>
</head>
<body>
	<div class="container">
		<h2>Facebook Birthday Calendar Filter</h2>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" role="form">

			<div class="form-group">
				<label for="url">Facebook Birthdays URL*</label> 
				<input type="url" name="url" id="url" value="<?php echo $url; ?>" class="form-control" />
			</div>

			<?php
			if ($url):
				$calendar = getcalendar($url);
			?>

			<div class="panel panel-default">
				<!-- Default panel contents -->
				<div class="panel-heading">Select Friends</div>
				<div class="panel-body">
					<p>Select friends below to <strong>exclude</strong> them from your birthday calendar.</p>
				</div>	
				<ul class="list-group" style="display:block;height:400px;overflow-y:auto;">
					<?php
					foreach ($calendar->VEVENT as $event) {
						$checked = !in_array($event->UID, $exclude) ?: ' checked="checked"';
						$name = str_replace('\'s Birthday', '', $event->SUMMARY);
						echo '<li class="list-group-item"><div class="checkbox"><label><input type="checkbox" name="exclude[]" value="'.$event->UID.'"'.$checked.'>' . $name .'</label></div></li>';
					}
					?>
				</ul>
			</div>
			<?php
			else:
				?>
			<p>
				Your list of friends will be shown after you have saved the Facebook URL.
			</p>
			<?php
			endif;
			?>

			<input type="submit" name="saveevents" value="Save" class="btn btn-primary" />

			<p>
				* See <a href="https://www.facebook.com/help/206619532710687">instructions</a> on Facebook Help.
			</p>

		</form>
	</div>
</body>
</html>