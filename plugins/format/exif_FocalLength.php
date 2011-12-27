<?php

class pb_exif_FocalLength {
	
	function format($value) {
		
		return papt_photoTaxonomies::frac2Dec( $value ).'mm';
	}
}

?>