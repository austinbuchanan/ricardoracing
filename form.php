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
				
				alert( "Ready!" );
							
				$("#horses-form").submit(function(event) {
				
				document.getElementById('progress').innerHTML = "";
				
				var previous = "";
				
				var ajaxInterval = setInterval(function() {
					var ajax = new XMLHttpRequest();
					ajax.onreadystatechange = function() {
						if (ajax.readyState == 4) {
							if (ajax.responseText != previous) {
								document.getElementById('progress').innerHTML = "\n" + previous;
								previous = ajax.responseText + "\n";
							}
						}
					};
					ajax.open("POST", "progress.txt", true);
					ajax.send();
				}, 300);

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
						alert('Success!');
						previous = "";
						document.getElementById('progress').innerHTML = "";
						clearInterval(ajaxInterval);
					});
				});
			});
		</script>
    </head>
    <body>
		<div id="heading">
			<h1>Quarter Horse Web Scraper</h1>
			<p>This application allows the user to run multiple queries to AllBreedPedigree.com. It removes all horses that are no quarter horses and returns results in a .csv file that can be viewed in Excel.</p>
		</div>
		<div id="horse_form">
			<form id="horses-form" action="scraper.php" title="" method="post">
				<table>
					<tr>
						<td>
							<label><strong>Horse Names:</strong></label>
							<br />
						</td>
					</tr>
					<tr>
						<td>
							<textarea rows="13" id="horsenames"></textarea>
						</td>
					<tr>
						<td>
							<label><strong>Filter:</strong></label>
						</td>
						<td>
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
							<label><strong>Generations:</strong></label>
						</td>
						<td>
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
							<label><strong>Crosses:</strong></label>
						</td>
						<td>
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
							<label><strong>Influence:</strong></label>
						</td>
						<td>
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
						<td>
						</td>
					</tr>
				</table>
			</form>
			<br />
		</div>
		<div id="output">
			<a href="horses.csv" download id="download" hidden></a>
		</div>
		
		<div id="progress"></div>
        
    </body>
</html>
