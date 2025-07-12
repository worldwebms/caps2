<?php
// fool the http server and client browser into thinking the file name
// passed in is coming back as a application attachment to save  as a file
	$url= "../invoices";
	//break the name to variables
	$TheFile="$url/$file";
	Header ( "Content-Type: application/octet-stream"); 
	Header ( "Content-Length: ".filesize($TheFile)); 
	Header( "Content-Disposition: attachment; filename=$file"); 
	readfile($TheFile); 
?>
