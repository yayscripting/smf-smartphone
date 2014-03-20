<?php 

if(isset($_GET['mode'])){

	// set cookie for 12 months
	setcookie("gmot_mode", $_GET['mode'], time() + 60*60*24*365);
	
	if(@substr($_GET['url'], 0, 1) == '/'){
	
		// redirect
		header("Location: ".$_GET['url']);
		exit();
		
	}

}

// back to home
header("Location: /");

?>