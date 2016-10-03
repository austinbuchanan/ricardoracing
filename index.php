<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<form name="login-form" action="form.php" method="post">
    <table style="padding-top:60px;">
    <tr>
        <td>
            <label><strong>Username:</strong></label>
            <input type="text" name="username" id="username" />
        </td>
    </tr>
    <tr>
        <td>
            <label><strong>Password:</strong></label>
            <input type="password" name="password" id="password" />
        </td>
    </tr>
    <tr>
        <td>
            <input type="submit" value="Log in" id="login" />
        </td>
    </tr>
    </table>
</form>
</body>
</html>