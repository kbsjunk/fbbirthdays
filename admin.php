<!DOCTYPE html>
<?php

// -------------------------------------------- //

include 'functions.php';
$fb = new \Kitbs\FbBirthdays();
// -------------------------------------------- //

?>
<html lang="en">
<head>
	<title>Facebook Birthday Calendar Filter</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css" rel="stylesheet">
	<style type="text/css">
	.container { max-width: 750px; }
	.list-group-item { padding: 8px; }
	.list-group-item .checkbox { margin:0; }
	.panel-body { border-bottom: 1px solid #DDDDDD; }
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
			<li <?php echo $fb->thisTab() == 'finish' || $fb->thisTab() == false ? ' class="active"' : ''; ?>><a href="#facebook-setup" data-toggle="tab">Facebook Setup</a></li>
			<li><a href="#exclude-friends" data-toggle="tab">Facebook Friends</a></li>
			<li><a href="#include-friends" data-toggle="tab">Non-Facebook Friends</a></li>
		</ul>

		<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" role="form" style="margin:20px 0;" autocomplete="off" enctype="multipart/form-data">

			<div class="tab-content">
				<div class="tab-pane active" id="facebook-setup">
					<div class="panel panel-default">
						<div class="panel-heading">Facebook Settings</div>
						<div class="panel-body">

							<div class="form-group">
								<label for="url">Facebook Birthdays URL</label> 
								<input type="url" name="url" id="url" value="<?php echo $fb->config->url; ?>" class="form-control" />
								<p class="help-block">
									See <a href="https://www.facebook.com/help/206619532710687" target="_blank">instructions</a> on Facebook Help.
								</p>
							</div>	
						</div>
					</div>
					<div class="btn-group">
						<input type="submit" name="save[exclude-friends]" value="Save and Next &gt;" class="btn btn-primary" />
					</div>
				</div>
				<div class="tab-pane" id="exclude-friends">
					<?php
					if ($fb->loadCalendar()):
						?>

					<div class="panel panel-default">
						<!-- Default panel contents -->
						<div class="panel-heading">Facebook Birthdays</div>
						<div class="panel-body">
							<p>Check the box for friends below to <strong>exclude</strong> them from your birthday calendar.</p>
							<p>You can change also a friend's Facebook name to something more meaningful.</p>
						</div>	
						<ul class="list-group" style="display:block;height:400px;overflow-y:auto;">
							<?php
							foreach ($fb->calendar->VEVENT as $event) {

								$checked = $fb->config->excluded($event, ' checked');
								$name = $fb->justName($event->SUMMARY);
								$disabled = $fb->config->renamed($event, false, ' disabled');
								$greyed = $fb->config->excluded($event, ' greyed');

								echo
								'<li class="list-group-item">
								<div class="input-group">
								<span class="input-group-addon">
								<input type="checkbox" name="exclude[]" class="excludeme" value="'.$event->UID.'"'.$checked.'>
								</span>
								<input type="text" name="rename['.$event->UID.']"
								value="'.$fb->config->getRename($event, $name).'"
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
					<div class="btn-group">
						<input type="submit" name="save[exclude-friends]" value="Save" class="btn btn-default" />
						<input type="submit" name="save[include-friends]" value="Save and Next &gt;" class="btn btn-primary" />
					</div>
				</div>
				<div class="tab-pane" id="include-friends">
					<div class="panel panel-default">
						<!-- Default panel contents -->
						<div class="panel-heading">Include non-Facebook Birthdays</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-md-6">
									<p>Add friends below who are not on Facebook to <strong>include</strong> them in your birthday calendar.</p>
									<p>If you need more blank rows, click Save.</p>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="icsfile">Upload .ics</label> 
										<input type="file" name="icsfile" id="icsfile" />
									</div>
								</div>
							</div>


						</div>	
						<div style="display:block;height:400px;overflow-y:auto;">
							<table class="table">
								<thead>
									<tr>
										<th>Name</th>
										<th>Month</th>
										<th>Day</th>
									</tr>
								</thead>
								<tbody>
									<?php if (is_array($fb->config->include)) {
										foreach ($fb->config->include as $i => $newdate) { ?>
										<tr>
											<td><?php $fb->includeName($i, $newdate->name); ?></td>
											<td><?php $fb->monthPicker($i, $newdate->month); ?></td>
											<td><?php $fb->dayPicker($i, $newdate->day, $newdate->month); ?></td>
										</tr>
										<?php }
									} ?>
									<?php for ($i=0; $i < 4; $i++) { ?>
									<tr>
										<td><?php $fb->includeName($i); ?></td>
										<td><?php $fb->monthPicker($i); ?></td>
										<td><?php $fb->dayPicker($i); ?></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="btn-group">
						<input type="submit" name="save[include-friends]" value="Save" class="btn btn-default" />
						<input type="submit" name="save[finish]" value="Save and Finish" class="btn btn-primary" />
					</div>
				</div>

			</form>
		</div>
		<script src="//code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.2/js/bootstrap.min.js" type="text/javascript"></script>
		<script type="text/javascript">
		$(document).ready(function() {

			if (location.hash.substr(0,2) == "#!") {
				$("a[href='#" + location.hash.substr(2) + "']").tab("show");
			}
			$("a[data-toggle='tab']").on("shown", function (e) {
				var hash = $(e.target).attr("href");
				if (hash.substr(0,1) == "#") {
					location.replace("#!" + hash.substr(1));
				}
			});


			$('.bmonth').on('change', function() {
				var bday = $(this).closest('tr').find('.bday');
				var bdayval = bday.val();
				var days = 31;

				switch( $(this).val() ) {
					case '2':
					days = 29
					break;
					case '4':
					case '6':
					case '9':
					case '11':
					days = 30
					break;
				}

				if (bdayval > days) { bday.val(''); }

				for (var i = days+1; i <= 31; i++) {
					bday.children('.sel'+i).remove();
				}
				for (var i = parseInt(bday.children().last().val())+1; i <= days; i++) {
					bday.append('<option value="'+i+'" class="sel'+i+'">'+i+'</option>');
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

			$('.deleteinclude').on('click', function() {
				var namebox = $(this).closest('tr').find('.includenamebox');
				namebox.val('');
				$(this).prop('disabled', true);
			});

			$('.includenamebox').on('change', function() {
				var namebutton = $(this).closest('tr').find('.deleteinclude');
				if ($(this).val()) {
					namebutton.prop('disabled', false);
				}
				else {
					namebutton.prop('disabled', true);
				}

			});

			$('.excludeme').on('change', function() {
				$(this).closest('li').find('.namebox').toggleClass('greyed', $(this).prop('checked'));
			});

		});
</script>
</body>
</html>