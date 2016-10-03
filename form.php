<?php
//get username and password from login.php
session_start();
$_SESSION['username'] = $_POST['username'];
$_SESSION['password'] = $_POST['password'];

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
		<!--<script src="jquery.js"></script>-->
        <script>
        </script>
    </head>
    <body>
	<div id="horse_form">
		<form name="horses-form" action="scraper.php" method="post">
			<table style="padding-top:60px;"
				<tr>
					<td>
						<label><strong>Enter a horse's name on each line:</strong><label>
						<br />
					</td>
				</tr>
				<tr>
					<td>
						<textarea rows="30" name="horsenames"></textarea>
						<br />
						<label><strong>Enter a filter:</strong></label>
						<select name="filter">
							<option value="Cross+Dups">Cross Dups</option>
							<option value="Dups+Only">Dups Only</option>
							<option value="All+Horses">All Horses</option>
							<option value="Males+Only">Males Only</option>
							<option value="Females+Only">Females Only</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						<label><strong>Enter the number of generations:</strong></label>
						<select name="gens">
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
						<select name="crosses">
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
						<select name="influence">
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
		<!--<script src="jquery.js"></script>
        <div id="processor"></div>-->
    </body>
</html>
