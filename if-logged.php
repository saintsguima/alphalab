<?php 
	session_start();
	require_once 'Globals/globals.php';
    if ($_SESSION["NOME"] == ""){
		header("Location: " . $GLOBALS['HOST'] . $GLOBALS['APP_HOST'] . "index.php");
	}
?>