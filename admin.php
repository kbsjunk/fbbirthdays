<!DOCTYPE html>
<?php

$url = false;
$exclude = false;
$include = false;
$rename = false;
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
if (isset($_POST['rename']) && $url) {
	$rename = array();
	$renames = (array) $_POST['rename'];
	$calendar = getcalendar($url);
	foreach ($calendar->VEVENT as $event) {
		if (@$renames[(string)$event->UID] != justname($event->SUMMARY)) {
			$rename[(string) $event->UID] = @$renames[(string)$event->UID];
		}
	}
}
if (isset($_POST['include'])) {
	$include = array();
	foreach ($_POST['include'] as $newdate) {
		if ($newdate['name']) {
			$include[md5($newdate['name'])] = $newdate;
		}
	}
}

if (isset($_POST['saveevents'])) {
	saveconfig($url, $exclude, $include, $rename);
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
	.greyed {
		background-color: #EEEEEE;
		color: #555555;
	}
	</style>
</head>
<body>
	<div class="container">
		<h2>Facebook Birthday Calendar Filter</h2>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#facebook-setup" data-toggle="tab">Facebook Setup</a></li>
			<li><a href="#exclude-friends" data-toggle="tab">Facebook Friends</a></li>
			<li><a href="#include-friends" data-toggle="tab">Non-Facebook Friends</a></li>
		</ul>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" role="form" style="margin:20px 0;">

			<div class="tab-content">
				<div class="tab-pane active" id="facebook-setup">

					<div class="panel panel-default">
						<div class="panel-heading">Facebook Settings</div>
						<div class="panel-body">

							<div class="form-group">
								<label for="url">Facebook Birthdays URL</label> 
								<input type="url" name="url" id="url" value="<?php echo $url; ?>" class="form-control" />
								<p class="help-block">
									See <a href="https://www.facebook.com/help/206619532710687" target="_blank">instructions</a> on Facebook Help.
								</p>
							</div>	
						</div>
					</div>
				</div>
				<div class="tab-pane" id="exclude-friends">
					<?php
					if ($url):
						$calendar = getcalendar($url);
					?>

					<div class="panel panel-default">
						<!-- Default panel contents -->
						<div class="panel-heading">Facebook Birthdays</div>
						<div class="panel-body">
							<p>Check the box for friends below to <strong>exclude</strong> them from your birthday calendar, or change their Facebook name to something more meaningful.</p>
						</div>	
						<ul class="list-group" style="display:block;height:400px;overflow-y:auto;">
							<?php
							foreach ($calendar->VEVENT as $event) {

								$checked = !in_array((string)$event->UID, $exclude) ?: ' checked="checked"';
								$name = justname($event->SUMMARY);
								$disabled = isset($rename[(string)$event->UID]) ?: ' disabled'; // && @$rename[(string)$event->UID] != $name
								$greyed = !in_array((string)$event->UID, $exclude) ?: ' greyed';

								echo
								'<li class="list-group-item">
								<div class="input-group">
								<span class="input-group-addon">
								<input type="checkbox" name="exclude[]" class="excludeme" value="'.((string)$event->UID).'"'.$checked.'>
								</span>
								<input type="text" name="rename['.$event->UID.']"
								value="'.(isset($rename[(string)$event->UID]) ? $rename[(string)$event->UID] : $name).'"
								data-oldname="'.$name.'"
								class="form-control namebox'.$greyed.'" />
								<span class="input-group-btn">
								<button class="btn btn-default undorename" type="button"'.$disabled.'>Undo</button>
								</span>
								</div>
								</li>';
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
				</div>
				<div class="tab-pane" id="include-friends">
					<div class="panel panel-default">
						<!-- Default panel contents -->
						<div class="panel-heading">Include non-Facebook Birthdays</div>
						<div class="panel-body">
							<p>Add friends below who are not on Facebook to <strong>include</strong> them in your birthday calendar.</p>
						</div>	
						<table class="table">
							<thead>
								<tr>
									<th>Name</th>
									<th>Month</th>
									<th>Day</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($include as $i => $newdate) { ?>
								<tr>
									<td><?php includeName($i, @$newdate['name']); ?></td>
									<td><?php monthPicker($i, @$newdate['month']); ?></td>
									<td><?php dayPicker($i, @$newdate['day']); ?></td>
								</tr>
								<?php } ?>
								<?php for ($i=0; $i < 2; $i++) { ?>
								<tr>
									<td><?php includeName($i); ?></td>
									<td><?php monthPicker($i); ?></td>
									<td><?php dayPicker($i); ?></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<input type="submit" name="saveevents" value="Save" class="btn btn-primary" />
			</form>
		</div>
		<script src="//code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js" type="text/javascript"></script>
		<script type="text/javascript">
		$(document).ready(function() {

			$('.bmonth').on('change', function() {
				var bday = $(this).closest('tr').find('.bday');

				switch( $(this).val() ) {
					case '2':
					bday.find('.sel30, .sel31').hide();
					break;
					case '4':
					case '6':
					case '9':
					case '11':
					bday.find('.sel30').show();
					bday.find('.sel31').hide();
					break;
					default:
					bday.find('.sel30, .sel31').show();
				}
			});

			$('.namebox').on('change', function() {
				var namebutton = $(this).closest('li').find('.undorename');
				if ($(this).val() != $(this).data('oldname')) {
					namebutton.prop('disabled', false);
				}
				else {
					namebutton.prop('disabled', true);
				}

			});

			$('.undorename').on('click', function() {
				var namebox = $(this).closest('li').find('.namebox');
				namebox.val(namebox.data('oldname'));
				$(this).prop('disabled', true);
			});

			$('.excludeme').on('change', function() {
				$(this).closest('li').find('.namebox').toggleClass('greyed', $(this).prop('checked'));
			});

		});
</script>
</body>
</html>