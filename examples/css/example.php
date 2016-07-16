<?php
	// This example minifies file "example.css" and displays its minified contents
	// The original contents of the file are displayed only when working in non-cli mode
	require ( "../../CssMinifier.phpclass" ) ;

	$input	=  "example.css" ;

	$webmode	=  ( php_sapi_name ( )  !=  'cli' ) ;

	if  ( $webmode )
	   {
		echo "<h1>Contents of original file :</h1><pre>" ;
		$contents	=  htmlspecialchars ( file_get_contents ( $input ) ) ;
		echo "$contents</pre><br/><br/>" ;
		echo "<h1>Contents of minified file :</h1><pre>" ;
	    } 

	$minifier		=  new  CssMinifier ( ) ;
	$minified_contents	=  $minifier -> MinifyFrom ( $input ) ;
	echo $minified_contents ;

	if  ( $webmode ) 
		echo "</pre><br/><br/><h1>Size comparison :</h1><pre>" ; 
	else 
		echo "\n\nSize comparison :\n" ;

	echo "\tSource file   : " . filesize ( $input ) . " bytes\n" ;
	echo "\tMinified file : " . strlen ( $minified_contents ) . " bytes\n" ;
