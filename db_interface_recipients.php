<?php

require_once 'db.php';
require_once 'common.php';


// check user is logged in
if (login_ok() == 1) {
	
	$dbh = dbConnect();
	
	if (strcmp($_POST['task'], 'save') == 0)
	{
	}
	else if (strcmp($_POST['task'], 'restore') == 0)
	{
	}
	else
	{
		echo "<p>Fail. unrecognised task: {$_POST['task']}</p>";
	}
	
	$dbh = null;
	
} else {
	echo '<p>Fail. Could not verify user. Login again.</p>';
}
