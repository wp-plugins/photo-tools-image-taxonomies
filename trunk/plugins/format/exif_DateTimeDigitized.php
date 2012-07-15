<?php

class pb_exif_DateTimeDigitized {
	
	function format($value) {
		
		$delim = substr($value, 4,1);
		
		switch ($delim) {
			
			case ':':
				list( $year, $month, $day, $timestamp ) = sscanf( $value, "%d:%d:%d %s" );
				break;
				
			case '-':
				list( $year, $month, $day, $timestamp ) = sscanf( $value, "%d-%d-%dT%s" );
	   		
				break;
		}
		
		// Make a new date string with Day, Month, Year
	    $time = date("H:i:s", strtotime($timestamp));
	    return "$month/$day/$year @ $time";
		
	}
}

?>