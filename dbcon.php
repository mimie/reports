<?php
$db = mysql_connect('localhost', 'admin', 'mysqladmin');

if (!$db) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("otrs_globe", $db);
?>
