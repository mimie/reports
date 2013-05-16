<?php
$db = mysql_connect('10.255.252.16', 'root', 'mysqladmin');

if (!$db) {
	die('Could not connect: ' . mysql_error());
}
mysql_select_db("otrs_globe", $db);
?>
