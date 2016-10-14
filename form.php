<?php
session_start();
$_SESSION['username'] = $_REQUEST['username'];
$_SESSION['password'] = $_REQUEST['password'];
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Horse Tracker</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="styles.css">
		<script src="jquery-3.1.1.min.js"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script type = "text/javascript">
		
			$( document ).ready(function() {
				
				alert( "ready!" );
				
				/*$( function() {
				var progressTimer,
				  progressbar = $( "#progressbar" ),
				  progressLabel = $( ".progress-label" ),
				  dialogButtons = [{
					text: "Cancel Download",
					click: closeDownload
				  }],
				  dialog = $( "#dialog" ).dialog({
					autoOpen: false,
					closeOnEscape: false,
					resizable: false,
					buttons: dialogButtons,
					open: function() {
					  progressTimer = setTimeout( progress, 2000 );
					},
					beforeClose: function() {
					  downloadButton.button( "option", {
						disabled: false,
						label: "Start Download"
					  });
					}
				  }),
				  downloadButton = $( "#submit-horses" )
					.button()
					.on( "click", function() {
					  $( this ).button( "option", {
						disabled: true,
						label: "Downloading..."
					  });
					  dialog.dialog( "open" );
					});
			 
				progressbar.progressbar({
				  value: false,
				  change: function() {
					progressLabel.text( "Current Progress: " + progressbar.progressbar( "value" ) + "%" );
				  },
				  complete: function() {
					progressLabel.text( "Complete!" );
					dialog.dialog( "option", "buttons", [{
					  text: "Close",
					  click: closeDownload
					}]);
					$(".ui-dialog button").last().trigger( "focus" );
				  }
				});
			 
				function progress() {
				  var val = progressbar.progressbar( "value" ) || 0;
			 
				  progressbar.progressbar( "value", val + Math.floor( Math.random() * 3 ) );
			 
				  if ( val <= 99 ) {
					progressTimer = setTimeout( progress, 50 );
				  }
				}
			 
				function closeDownload() {
				  clearTimeout( progressTimer );
				  dialog
					.dialog( "option", "buttons", dialogButtons )
					.dialog( "close" );
				  progressbar.progressbar( "value", false );
				  progressLabel
					.text( "Starting download..." );
				  downloadButton.trigger( "focus" );
				}
			  });*/
				
				$("#horses-form").submit(function(event) {
					
				/*var callAjax = function(){
					$.ajax({
						method:'get',
						data:
						dataType: "json",
						url:'scraper.php',
						success:function(data){
							response = jQuery.parseJSON(data);
							document.getElementById('progress').innerHTML += response;
						}
					});
				}
				
				setInterval(callAjax,3000);*/
				
				var previous = "";

				setInterval(function() {
					var ajax = new XMLHttpRequest();
					ajax.onreadystatechange = function() {
						if (ajax.readyState == 4) {
							if (ajax.responseText != previous) {
								document.getElementById('progress').innerHTML = previous + "<br>";
								previous = ajax.responseText;
							}
						}
					};
					ajax.open("POST", "progress.txt", true); //Use POST to avoid caching
					ajax.send();
				}, 1);

				event.preventDefault();

				var $form = $( this ),
				url = $form.attr( 'action' );

				var posting = $.post( url, 
				{ 
					horsenames: $('#horsenames').val(), 
					filter: $('#filter').val(),
					gens: $('#gens').val(),
					crosses: $('#crosses').val(),
					influence: $('#influence').val(),
				});

					posting.done(function( data ) 
					{
						document.getElementById('download').click();
						alert('success');
					});
				});
			});
		</script>
    </head>
    <body>
		
		<div id="horse_form">
			<form id="horses-form" action="scraper.php" title="" method="post">
				<table style="padding-top:60px;">
					<tr>
						<td>
							<label><strong>Enter a horse's name on each line:</strong><label>
							<br />
						</td>
					</tr>
					<tr>
						<td>
							<textarea rows="30" id="horsenames"></textarea>
							<br />
							<label><strong>Enter a filter:</strong></label>
							<select id="filter">
								<option value="Cross+Dups">Cross Dups</option>
								<option value="Dups+Only">Dups Only</option>
								<option value="Males+Only">Males Only</option>
								<option value="Females+Only">Females Only</option>
								<option value="All+Horses">All Horses</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label><strong>Enter the number of generations:</strong></label>
							<select id="gens">
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
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label><strong>Enter the number of crosses:</strong></label>
							<select id="crosses">
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
								<option value="9">9</option>
								<option value="10">10</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							<label><strong>Enter the influence:</strong></label>
							<select id="influence">
								<option value="All">All</option>
								<option value="> 2x3">> 2x3</option>
								<option value="> 3x3">> 3x3</option>
								<option value="> 3x4">> 3x4</option>
								<option value="> 4x4">> 4x4</option>
								<option value="> 4x5">> 4x5</option>
								<option value="> 5x5">> 5x5</option>
								<option value="> 5x6">> 5x6</option>
								<option value="All2">All</option>
							</select>
							
						</td>
					</tr>
					<tr>
						<td>
							<input id="submit-horses" type="submit" />
						</td>
					</tr>
				</table>
			</form>
		</div>
		<!--<div id="dialog" title="File Download">
		<div class="progress-label">Starting download...</div>
		<div id="progressbar"></div>
		</div>-->
		<div id="progress"></div>
		<div id="output">
			<a href="horses.csv" download id="download" hidden></a>
		</div>
        
    </body>
</html>
