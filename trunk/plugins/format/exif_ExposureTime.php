<?php

class pb_exif_ExposureTime {
	
	function format($value) {
	
		if (strpos($value, '/')) {
			$fn = create_function("", "return ({$value});" );
    		$value = $fn();
		}
	
		if ((1 / $value) > 1) {
	    	if ((number_format((1 / $value), 1)) == 1.3
	       	or number_format((1 / $value), 1) == 1.5
	        or number_format((1 / $value), 1) == 1.6
	        or number_format((1 / $value), 1) == 2.5) {
	        	$pshutter = "1/" . number_format((1 / $value), 1, '.', '');
	        } else {
	        	$pshutter = "1/" . number_format((1 / $value), 0, '.', '');
	        }
	    } else {
	    	
	    	$pshutter = $value;
	    }
	    
	    return sprintf("%s sec (%s)", round(papt_photoTaxonomies::frac2dec($pshutter),3), $pshutter);

	}
}

?>