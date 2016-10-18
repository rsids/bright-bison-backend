<?php
session_start();
include_once('../Bright.php');
//include_once('Bright/page/Backup.php');
$backup = new Backup();

if(!isset($_GET['id']) || !isset($_GET['fields']))
	exit;
echo 'Restoring to backupId ' . (int)$_GET['id'];
$backup -> restoreBackup((int)$_GET['id'], explode(',', $_GET['fields']));