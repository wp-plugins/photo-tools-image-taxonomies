<?php

class pb_exif_ExposureBiasValue {
	
	function format($value) {
		
		return papt_photoTaxonomies::frac2Dec($value)." EV";

	}
}

?>