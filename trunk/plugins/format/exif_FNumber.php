<?php

class pb_exif_FNumber {
	
	function format($value) {
		
		return "f/".round(papt_photoTaxonomies::frac2Dec($value),2);
	}
}

?>