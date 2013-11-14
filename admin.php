<!DOCTYPE html>
<?php

$url = false;
$exclude = false;
$include = false;
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
// if (isset($_POST['include'])) {
// 	foreach ($_POST['include'] as $newdate) {
// 	$include[$]	
// 	}
// }
if (isset($_POST['saveevents'])) {
	saveconfig($url, $exclude, $include);
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
		<ul class="nav nav-tabs">
			<li class="active"><a href="#facebook-setup" data-toggle="tab">Facebook Setup</a></li>
			<li><a href="#exclude-friends" data-toggle="tab">Exclude Friends</a></li>
			<li><a href="#include-friends" data-toggle="tab">Include Friends</a></li>
		</ul>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" role="form" style="margin:20px 0;">

			<div class="tab-content">
				<div class="tab-pane active" id="facebook-setup">
					<div class="form-group">
						<label for="url">Facebook Birthdays URL*</label> 
						<input type="url" name="url" id="url" value="<?php echo $url; ?>" class="form-control" />
						<p class="help-block">
							* See <a href="https://www.facebook.com/help/206619532710687" target="_blank">instructions</a> on Facebook Help.
						</p>
					</div>	
				</div>
				<div class="tab-pane" id="exclude-friends">
					<?php
					if ($url):
						$calendar = getcalendar($url);
					?>

					<div class="panel panel-default">
						<!-- Default panel contents -->
						<div class="panel-heading">Exclude Facebook Birthdays</div>
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
									<th>Month/ Day</th>
								</tr>
							</thead>
							<tbody>
								<?php for ($i=0; $i < 2; $i++): ?>
								<tr>
									<td><input type="text" name="include[][name]" class="form-control" placeholder="Name" /></td>
									<td>
										<div class="input-group">
											<select name="include[][month]" class="form-control bmonth">
												<option></option>
												<option value="1">January</option>
												<option value="2">February</option>
												<option value="3">March</option>
												<option value="4">April</option>
												<option value="5">May</option>
												<option value="6">June</option>
												<option value="7">July</option>
												<option value="8">August</option>
												<option value="9">September</option>
												<option value="10">October</option>
												<option value="11">November</option>
												<option value="12">December</option>
											</select> <select name="include[][day]" class="form-control bday">
												<option></option>
												<option value="1">1</option>
												<option value="2">2</option>
												<option value="3">3</option>
												<option value="4">4</option>
												<option value="5">5</option>
												<option value="6">6</option>
												<option value="7">7</option>
												<option value="8">8</option>
												<option value="9">9</option>
												<option value="10">10</option>
												<option value="11">11</option>
												<option value="12">12</option>
												<option value="13">13</option>
												<option value="14">14</option>
												<option value="15">15</option>
												<option value="16">16</option>
												<option value="17">17</option>
												<option value="18">18</option>
												<option value="19">19</option>
												<option value="20">20</option>
												<option value="21">21</option>
												<option value="22">22</option>
												<option value="23">23</option>
												<option value="24">24</option>
												<option value="25">25</option>
												<option value="26">26</option>
												<option value="27">27</option>
												<option value="28">28</option>
												<option class="sel29" value="29">29</option>
												<option class="sel30" value="30">30</option>
												<option class="sel31" value="31">31</option>
											</select>
										</div>
									</td>
								</tr>
							<?php endfor; ?>
						</tbody>
					</table>
					<ul class="list-group" style="display:block;height:400px;overflow-y:auto;">
						<?php
					//foreach ($calendar->VEVENT as $event) {
						//$checked = !in_array($event->UID, $exclude) ?: ' checked="checked"';
						//$name = str_replace('\'s Birthday', '', $event->SUMMARY);
						//echo '<li class="list-group-item"><div class="checkbox"><label><input type="checkbox" name="include['.$bdate.']" value="'.$event->UID.'"'.$checked.'>' . $name .'</label></div></li>';
					//}
						?>
					</ul>
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

	});
	</script>
</body>
</html>