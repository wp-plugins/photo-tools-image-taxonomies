<?php
/*
Plugin Name: PhotoPress - Image Taxonomies
Plugin URI: Permalink: http://www.peteradamsphoto.com/?page_id=3148
Description: Adds multiple photo related meta-data taxonomies to your uploaded images.
Author: Peter Adams
Version: 1.9
Author URI: http://www.peteradamsphoto.com 
*/

/**
 * PhotoPress Taxonomies
 *
 * This class does the heavy lifting of extracting and accessing an image
 * files embedded iptc, exif, and XMP meta data.
 */
class papt_photoTaxonomies {
	
	var $plugin_dir = 'plugins/';
	// raw xmp array
	var $xmp		= array();
	// flattened xmp array
	var $flat_xmp;
	var $iptc 		= array();
	var $exif 		= array();
	var $labels 	= array();
	
	function __construct() {
	
		$this->plugin_dir = dirname(__FILE__).'/plugins';
		return;
	}
	
	function get ( $keys ) {
	
		$pairs = array();
	
		if ( ! is_array( $keys ) ) {
			
			$keys = array( $keys );
		}
		
		foreach ( $keys as $key ) {
			
			list ( $family, $attr ) = explode( ':', trim( $key ) );
			
			if ( $family === 'exif') {
				
				$value = $this->getExif( $attr );
				
			} elseif ( $family === 'iptc') {
			
				$value = $this->getIptc( $attr );
			
			} elseif ( $family === 'photopress') {
				
				$method = 'get'.ucwords($attr);
				if ( method_exists( $this, $method) ) {
				
					$value = $this->$method( $attr );
				} else {
					
					$value = 'not found';
				}
			} else {
				
				$value = $this->getXmp( $attr );
			}
			
			$pairs[ $this->getLabel( $key ) ] = $value;	
		}
		
		return $pairs;
	}
		
	function getRawXmpValues() {
		
		return $this->xmp;
	}
	
	function getTitle() {
	
		$title = $this->getXmp( 'dc:title' );
		
		if ( ! $title ) {
			$title = $this->getIptc( 'title' );
		}
		
		return $title;
	}
	
	function getIptc( $name ) {
		
		if ( isset( $this->iptc[$name] ) ) {
			return $this->iptc[$name];
		}
	}
	
	function getExif( $name ) {
		
		if ( isset( $this->exif[$name] ) ) {
			return $this->formatKeyValue('exif:'.$name, $this->exif[$name]);
		}
	}
	
	function getCaption() {
		return $this->getXmp('dc:description');
	}
	
	function getKeywords() {
		return $this->getXmp('dc:subject');
	}
	
	function getGeoValues() {
		
		return array('city' => $this->getXmp('photoshop:City'),
					 'state' => $this->getXmp('photoshop:State'),
					 'country' => $this->getXmp('photoshop:Country')
					);
	}
	
	function getCity() {
	
		return $this->getXmp('photoshop:City');
	}
	
	function getState() {
	
		return $this->getXmp('photoshop:State');
	}
	
	function getCountry() {
	
		return $this->getXmp('photoshop:Country');
	}
	
	function getShutterSpeed() {
		
		$ss = $this->getExif('ExposureTime');
		
		if ( ! $ss ) {
			$ss = $this->getXmp('exif:ExposureTime');
		}
		return $ss;
	}
	
	function getCreationDate() {
		
		return $this->getXmp('exif:DateTimeDigitized');
	}
	
	function getCopyrightHolder() {
	
		// get copyright holder
		$copyright = $this->getXmp( 'dc:creator' );
		if ( ! $copyright ) {
			$copyright = $this->getExif( 'Copyright' );
		}
		
		return $copyright;
	}
	
	function getContactUrl() {
		// get creator URL
		$bucket = $this->getXmp('Iptc4xmpCore:CreatorContactInfo');
		if ( $bucket ) {
			$url = $bucket['Iptc4xmpCore:CiUrlWork'];
		}
		
		return $url;
	}

	function getRightsStatement() {
			
		// get rights statement
		$rights = $this->getXmp('xmpRights:UsageTerms');
		if ( $rights ) {
			$rights = $rights[0];
		}
				 
		return $rights;
	}
	
	function getCamera() {
		
		$camera = $this->getExif('Make') . ' ' . $this->getExif('Model');
		
		if (! $camera ) {
			$this->getXmp('tiff:Model');
		}
		
		return $camera;
	}
	
	function getLens() {

		return $this->getXmp('aux:Lens');
	}
	
	function getAllXmp() {
		
		return $this->flat_xmp;
	}
	
	function getAllMetaData() {
		
		$meta = array();
		$meta['xmp'] = $this->flat_xmp;
		$meta['exif'] = $this->exif;
		$meta['iptc'] = $this->iptc;
		
		return $meta;
	}
	
	function getXmp($name) {
		
		$nvalue = '';
		$all_xmp = $this->getAllXmp();
		
		if ($all_xmp) {
		
			if (is_array($name)) {
			
				$names = array_flip($name);
				$somevalues = array_intersect_key($all_xmp,$names);
				$nvalue = array();
				foreach ($somevalues as $k => $v) {
					$val = $this->formatKeyValue($k, $v);
				}
				
				$nvalue[$k] = $val;
				
			} else {
			
				if (array_key_exists($name, $all_xmp)) {
					
					$nvalue = $all_xmp[$name];
					
					// just in case the value is a loner value in an array
					if (is_array($nvalue) && (count($nvalue) < 2)) {
					
						$nvalue = $nvalue[0];
					}			
					
					$nvalue = $this->formatKeyValue($name, $nvalue);	
				}
			}
			
			return $nvalue;	
		}
	}
	
	function displayAllXmp() {
	
		return $this->makeXmpHtml($this->getAllXmp());
	}
	
	function displayXmp($values, $template = '') {
		
		$nvalues = $this->getXmp($values);
		
		return $this->displayMeta($values, $template);
	}
	
	function displayMeta($values, $class = '', $template = '', $container_template = '' ) {
		
		if ( $values ) {
		
			if ( is_array( $values ) ) {
				
				return $this->makeXmpHtml( $values, $class, $template, $container_template );
				
			} else {
			
				return $this->makeXmpHtml( array( $values => $values ), $class, $template, $container_template );
			}
		}
	}
	
	function render( $values, $class = '', $template = '', $container_template = '' ) {
		
		return $this->displayMeta( $values, $class, $template, $container_template );
	}
	
	function makeXmpHtml( $values, $class = '', $template = '', $container_template = '' ) {
		
		if ( $values ) {
		
			if ( ! $class ) {
				
				$class = 'table-display';
			}
		
			if ( ! $template ) {
			
				$container_template = '<dl class="'. $class .'">%s</dl>';
			
				$template = '<DT>%s:</DT><DD>%s</DD>';
			}
			
			$md = '';
		
			foreach ($values as $k => $v) {
			
				$i = 0;
							
				if ($v) {
				
					if (is_array($v)) {
						$v = implode(', ', $v);
					}
					
					$md .= sprintf($template, $this->getLabel($k), $v);
					$i++;
				}
			}
			
			if ( $i > 0 ) {
			
				$md = sprintf( $container_template, $md );
			}
			
			return $md;
			
		} else {
		
			return false;
		}
					
	}
		
	function loadFromFile($file) {
		
		$this->xmp = $this->extractXmp($file);
		$this->flat_xmp = $this->flattenXmp($this->xmp);
		//$this->iptc = wp_read_image_metadata( $file );
		//print_r($xml_array);
		$this->exif = @exif_read_data( $file );			
	}
	
	function loadFromSerializedString($str) {
		
		$md = unserialize($str);
		$this->flat_xmp = $md;
	}
	
	function loadFromArray($array) {
		
		if (isset( $array['xmp'] ) ) {
		
			$this->flat_xmp = $array['xmp'];
		}
		
		if (isset( $array['exif'] ) ) {
		
			$this->exif = $array['exif'];
		}
		
		if (isset( $array['iptc'] ) ) {
		
			$this->iptc = $array['iptc'];
		}
	}
	
	function flattenXmp($xmp) {
		$nxmp = array();
		
		foreach ($xmp as $k => $v) {
		
			if ($k === 'rdf:Description') {
				$nxmp = array_merge($v, $nxmp);
			} else {
				$nxmp[$k] = $v;
			}
		}
		
		return $nxmp;
	}
	
	function extractXmp($file) {

		$xml_array = array();
		//TODO:Require a lot of memory, could be better
		ob_start();
		@readfile($file);
		$source = ob_get_contents();
		ob_end_clean();
		$source;
		$start = strpos( $source, "<x:xmpmeta"   );
		$end   = strpos( $source, "</x:xmpmeta>" );
		if ((!$start === false) && (!$end === false)) {
			$lenght = $end - $start;
			$xmp_data = substr($source, $start, $lenght+12 );
			unset($source);
			//print_r($xmp_data);
			$xml_array = $this->XMP2Array($xmp_data);
		} 
		
		unset($source);
		return $xml_array;
	}
	
	function XMP2array($data) {
				
		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); // Dont mess with my cAsE sEtTings
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); // Dont bother with empty info
		xml_parse_into_struct($parser, $data, $values);
		xml_parser_free($parser);
		//print_r($values);
		
		$xmlarray			= array();	// The XML array
		$xmp_array  		= array();	// The returned array
		$stack        		= array();	// tmp array used for stacking
		$list_array   		= array();	// tmp array for list elements
		$list_element 		= false;	// rdf:li indicator
		$temp_attr 			= array();
		$last_open_tag 		= '';
		
		foreach($values as $val) {
			
		  	if($val['type'] === "open") {
		  		
		  			
			      	if ($val['attributes']) {
			      		$temp_attr[$val['tag']] = $val['attributes'];
			      	} else {
			      		array_push($stack, $val['tag']);
			      	}	
			      	$last_open_tag = $val['tag'];
		  		
		      	
		      	
		    } elseif($val['type'] === "close") {
		    	// reset the compared stack
		    	if ($list_element == false) {
		    		if (!$stack['value']) {
		    			if (array_key_exists($val['tag'], $temp_attr)) {
		    				$xmlarray[$val['tag']] = $temp_attr[$val['tag']];
		    			}
		      				
		      		}
		      	}
		      	$last_open_tag = '';
		      	array_pop($stack);
		      	// reset the rdf:li indicator & array
		      	$list_element = false;
		      	$list_array   = array();
		      	
		    } elseif($val['type'] === "complete") {
				if ($val['tag'] === "rdf:li") {
					// first go one element back
					if ($list_element == false)
						array_pop($stack);
						
					$list_element = true;
					// save it in our temp array
					$list_array[] = $val['value']; 
					//print_r( $val['value']);
					// in the case it's a list element we seralize it
					//$value = implode(",", $list_array);
					$this->setArrayValue($xmlarray, $stack, $list_array);

					
		      	} else {
		      		array_push($stack, $val['tag']);
		      		if (array_key_exists('value', $val)) {
		      			$this->setArrayValue($xmlarray, $stack, $val['value']);
		      		} elseif (array_key_exists('attributes', $val)){
		      			$xmlarray[$val['tag']] = $val['attributes'];
		      		}
		      		array_pop($stack);
		      	}
		    }
		    
		} // foreach
		
		// cut off the useless tags
		$strip_keys = array('x:xmpmeta','rdf:RDF');
		
		foreach ($strip_keys as $k) {
			unset($xmlarray[$k]);
		}
		
		
		//$xmlarray = $this->exchangeKeys($xmlarray); 
		//print_r($xmlarray);
		return $xmlarray;
	}
		
	function setArrayValue(&$array, $stack, $value) {
	
		if ($stack) {
			$key = array_shift($stack);
			//print $key;
			//TODO:Review this, reports sometimes a error "Fatal error: Only variables can be passed by reference" (PHP 5.2.6)
			
	    	$this->setArrayValue($array[$key], $stack, $value);
	    	
	    	return $array;
	  	} else {
	    	$array = $value;
	    	
	    	
	  	}
	}
	
	function formatKeyValue($key, $value) {
		
		if (!empty($value)) {
			
			$name = str_replace(":", "_", $key);
			$file = $this->plugin_dir.'/format/'.$name.'.php';
			
			if(file_exists($file)){
				//print $file;	
				require_once($file);
				$class_name = 'pb_'.$name; 
				//print $class_name;
				$f = new $class_name;
				return $f->format($value);
			} else {
				return $value;
			}
			
		}		
		return false;
	}
	
	static function frac2dec($str) {
		
		if ( strpos($str, '/') ) {
		
			@list( $n, $d ) = explode( '/', $str );
			if ( !empty($d) ) {
				return $n / $d;
			}
		
		}
		
		return $str;
	}
	
	/**
	 * Convert the exif date format to a unix timestamp.
	 *
	 * @param string $str
	 * @return int
	 */
	function date2ts($str) {
		@list( $date, $time ) = explode( ' ', trim($str) );
		@list( $y, $m, $d ) = explode( ':', $date );
	
		return strtotime( "{$y}-{$m}-{$d} {$time}" );
	}
	
	function getLabel($str) {
	
		if ( ! $labels ) {
			
			$this->labels = $this->getAlllabels();	
		}
	
		
		if ( array_key_exists( $str, $this->labels ) ) {
		
			return $this->labels[ $str ];
			
		} else {
		
			return $str;
		}
		
	}
	
	function getAllLabels() {
		
		return array(

		"dc:contributor" 					=> "Other Contributor(s)",
		"dc:coverage" 						=> "Coverage (scope)",
		"dc:creator" 						=> "Creator(s) (Authors)",
		"dc:date" 							=> "Date",
		"dc:description"			 		=> "Caption",
		"dc:format" 						=> "MIME Data Format",
		"dc:identifier" 					=> "Unique Resource Identifer",
		"dc:language" 						=> "Language(s)",
		"dc:publisher" 						=> "Publisher(s)",
		"dc:relation" 						=> "Relations to other documents",
		"dc:rights" 						=> "Rights Statement",
		"dc:source" 						=> "Source (from which this Resource is derived)",
		"dc:subject" 						=> "Keywords",
		"dc:title" 							=> "Title",
		"dc:type" 							=> "Resource Type",
		
		"aux:Lens" 							=> "Lens",
		
		"xmp:Advisory" 						=> "Externally Editied Properties",
		"xmp:BaseURL" 						=> "Base URL for relative URL's",
		"xmp:CreateDate"			 		=> "Original Creation Date",
		"xmp:CreatorTool" 					=> "Creator Tool",
		"xmp:Identifier" 					=> "Identifier(s)",
		"xmp:MetadataDate" 					=> "Metadata Last Modify Date",
		"xmp:ModifyDate" 					=> "Resource Last Modify Date",
		"xmp:Nickname" 						=> "Nickname",
		"xmp:Thumbnails"			 		=> "Thumbnails",
		
		"xmpidq:Scheme" 					=> "Identification Scheme",
		
		// These are not in spec but Photoshop CS seems to use them
		"xap:Advisory" 						=> "Externally Editied Properties",
		"xap:BaseURL" 						=> "Base URL for relative URL's",
		"xap:CreateDate" 					=> "Original Creation Date",
		"xap:CreatorTool" 					=> "Creator Tool",
		"xap:Identifier" 					=> "Identifier(s)",
		"xap:MetadataDate" 					=> "Metadata Last Modify Date",
		"xap:ModifyDate" 					=> "Resource Last Modify Date",
		"xap:Nickname" 						=> "Nickname",
		"xap:Thumbnails" 					=> "Thumbnails",
		"xapidq:Scheme" 					=> "Identification Scheme",
		
		"xapRights:Certificate"			 	=> "Certificate",
		"xapRights:Copyright" 				=> "Copyright",
		"xapRights:Marked" 					=> "Marked",
		"xapRights:Owner" 					=> "Owner",
		"xapRights:UsageTerms" 				=> "Legal Terms of Usage",
		"xapRights:WebStatement" 			=> "Web Page describing rights statement (Owner URL)",
		
		"xapMM:ContainedResources" 			=> "Contained Resources",
		"xapMM:ContributorResources" 		=> "Contributor Resources",
		"xapMM:DerivedFrom" 				=> "Derived From",
		"xapMM:DocumentID" 					=> "Document ID",
		"xapMM:History" 					=> "History",
		"xapMM:LastURL" 					=> "Last Written URL",
		"xapMM:ManagedFrom"		 			=> "Managed From",
		"xapMM:Manager" 					=> "Asset Management System",
		"xapMM:ManageTo" 					=> "Manage To",
		"xapMM:xmpMM:ManageUI" 				=> "Managed Resource URI",
		"xapMM:ManagerVariant" 				=> "Particular Variant of Asset Management System",
		"xapMM:RenditionClass" 				=> "Rendition Class",
		"xapMM:RenditionParams"		 		=> "Rendition Parameters",
		"xapMM:RenditionOf" 				=> "Rendition Of",
		"xapMM:SaveID" 						=> "Save ID",
		"xapMM:VersionID" 					=> "Version ID",
		"xapMM:Versions" 					=> "Versions",
		
		"xapBJ:JobRef" 						=> "Job Reference",
		
		"xmpTPg:MaxPageSize"	 			=> "Largest Page Size",
		"xmpTPg:NPages" 					=> "Number of pages",
		
		"pdf:Keywords" 						=> "Keywords",
		"pdf:PDFVersion"			 		=> "PDF file version",
		"pdf:Producer" 						=> "PDF Creation Tool",
		
		"photoshop:AuthorsPosition" 		=> "Authors Position",
		"photoshop:CaptionWriter"			=> "Caption Writer",
		"photoshop:Category" 				=> "Category",
		"photoshop:City" 					=> "City",
		"photoshop:Country" 				=> "Country",
		"photoshop:Credit" 					=> "Credit",
		"photoshop:DateCreated" 			=> "Creation Date",
		"photoshop:Headline" 				=> "Headline",
		"photoshop:History" 				=> "History", // Not in XMP spec
		"photoshop:Instructions" 			=> "Instructions",
		"photoshop:Source" 					=> "Source",
		"photoshop:State" 					=> "State",
		"photoshop:SupplementalCategories" 	=> "Supplemental Categories",
		"photoshop:TransmissionReference" 	=> "Technical (Transmission) Reference",
		"photoshop:Urgency" => "Urgency",
		
		"tiff:ImageWidth" 					=> "Image Width",
		"tiff:ImageLength" 					=> "Image Height",
		"tiff:BitsPerSample" 				=> "Bits Per Sample",
		"tiff:Compression" 					=> "Compression",
		"tiff:PhotometricInterpretation" 	=> "Photometric Interpretation",
		"tiff:Orientation" 					=> "Orientation",
		"tiff:SamplesPerPixel" 				=> "Samples Per Pixel",
		"tiff:PlanarConfiguration" 			=> "Planar Configuration",
		"tiff:YCbCrSubSampling" 			=> "YCbCr Sub-Sampling",
		"tiff:YCbCrPositioning" 			=> "YCbCr Positioning",
		"tiff:XResolution" 					=> "X Resolution",
		"tiff:YResolution" 					=> "Y Resolution",
		"tiff:ResolutionUnit" 				=> "Resolution Unit",
		"tiff:TransferFunction" 			=> "Transfer Function",
		"tiff:WhitePoint" 					=> "White Point",
		"tiff:PrimaryChromaticities" 		=> "Primary Chromaticities",
		"tiff:YCbCrCoefficients" 			=> "YCbCr Coefficients",
		"tiff:ReferenceBlackWhite" 			=> "Black & White Reference",
		"tiff:DateTime" 					=> "Date & Time",
		"tiff:ImageDescription" 			=> "Image Description",
		"tiff:Make" 						=> "Make",
		"tiff:Model" 						=> "Camera",
		"tiff:Software" 					=> "Software",
		"tiff:Artist" 						=> "Artist",
		"tiff:Copyright" 					=> "Copyright",
		
		"exif:ExifVersion" 					=> "Exif Version",
		"exif:FlashpixVersion" 				=> "Flash pix Version",
		"exif:ColorSpace" 					=> "Color Space",
		"exif:ComponentsConfiguration" 		=> "Components Configuration",
		"exif:CompressedBitsPerPixel" 		=> "Compressed Bits Per Pixel",
		"exif:PixelXDimension" 				=> "Pixel X Dimension",
		"exif:PixelYDimension" 				=> "Pixel Y Dimension",
		"exif:MakerNote" 					=> "Maker Note",
		"exif:UserComment"					=> "User Comment",
		"exif:RelatedSoundFile" 			=> "Related Sound File",
		"exif:DateTimeOriginal" 			=> "Date & Time of Original",
		"exif:DateTimeDigitized" 			=> "Taken On",
		"exif:ExposureTime" 				=> "Shutter Speed",
		"exif:FNumber" 						=> "Aperture",
		"exif:ExposureProgram" 				=> "Exposure Program",
		"exif:SpectralSensitivity" 			=> "Spectral Sensitivity",
		"exif:ISOSpeedRatings" 				=> "ISO Speed",
		"exif:OECF" 						=> "Opto-Electronic Conversion Function",
		"exif:ShutterSpeedValue" 			=> "Shutter Speed Value",
		"exif:ApertureValue" 				=> "Aperture Value",
		"exif:BrightnessValue" 				=> "Brightness Value",
		"exif:ExposureBiasValue" 			=> "Exposure Bias Value",
		"exif:MaxApertureValue" 			=> "Max Aperture Value",
		"exif:SubjectDistance" 				=> "Subject Distance",
		"exif:MeteringMode" 				=> "Metering Mode",
		"exif:LightSource" 					=> "Light Source",
		"exif:Flash" 						=> "Flash",
		"exif:FocalLength" 					=> "Focal Length",
		"exif:SubjectArea" 					=> "Subject Area",
		"exif:FlashEnergy" 					=> "Flash Energy",
		"exif:SpatialFrequencyResponse" 	=> "Spatial Frequency Response",
		"exif:FocalPlaneXResolution" 		=> "Focal Plane X Resolution",
		"exif:FocalPlaneYResolution" 		=> "Focal Plane Y Resolution",
		"exif:FocalPlaneResolutionUnit" 	=> "Focal Plane Resolution Unit",
		"exif:SubjectLocation" 				=> "Subject Location",
		"exif:SensingMethod" 				=> "Sensing Method",
		"exif:FileSource" 					=> "File Source",
		"exif:SceneType" 					=> "Scene Type",
		"exif:CFAPattern" 					=> "Colour Filter Array Pattern",
		"exif:CustomRendered"				=> "Custom Rendered",
		"exif:ExposureMode" 				=> "Exposure Mode",
		"exif:WhiteBalance" 				=> "White Balance",
		"exif:DigitalZoomRatio" 			=> "Digital Zoom Ratio",
		"exif:FocalLengthIn35mmFilm" 		=> "Focal Length In 35mm Film",
		"exif:SceneCaptureType" 			=> "Scene Capture Type",
		"exif:GainControl" 					=> "Gain Control",
		"exif:Contrast" 					=> "Contrast",
		"exif:Saturation" 					=> "Saturation",
		"exif:Sharpness" 					=> "Sharpness",
		"exif:DeviceSettingDescription" 	=> "Device Setting Description",
		"exif:SubjectDistanceRange" 		=> "Subject Distance Range",
		"exif:ImageUniqueID" 				=> "Image Unique ID",
		"exif:GPSVersionID" 				=> "GPS Version ID",
		"exif:GPSLatitude" 					=> "GPS Latitude",
		"exif:GPSLongitude" 				=> "GPS Longitude",
		"exif:GPSAltitudeRef" 				=> "GPS Altitude Reference",
		"exif:GPSAltitude" 					=> "GPS Altitude",
		"exif:GPSTimeStamp" 				=> "GPS Time Stamp",
		"exif:GPSSatellites" 				=> "GPS Satellites",
		"exif:GPSStatus" 					=> "GPS Status",
		"exif:GPSMeasureMode" 				=> "GPS Measure Mode",
		"exif:GPSDOP" 						=> "GPS Degree Of Precision",
		"exif:GPSSpeedRef" 					=> "GPS Speed Reference",
		"exif:GPSSpeed" 					=> "GPS Speed",
		"exif:GPSTrackRef" 					=> "GPS Track Reference",
		"exif:GPSTrack" 					=> "GPS Track",
		"exif:GPSImgDirectionRef" 			=> "GPS Image Direction Reference",
		"exif:GPSImgDirection" 				=> "GPS Image Direction",
		"exif:GPSMapDatum" 					=> "GPS Map Datum",
		"exif:GPSDestLatitude" 				=> "GPS Destination Latitude",
		"exif:GPSDestLongitude" 			=> "GPS Destination Longitude",
		"exif:GPSDestBearingRef" 			=> "GPS Destination Bearing Reference",
		"exif:GPSDestBearing" 				=> "GPS Destination Bearing",
		"exif:GPSDestDistanceRef" 			=> "GPS Destination Distance Reference",
		"exif:GPSDestDistance" 				=> "GPS Destination Distance",
		"exif:GPSProcessingMethod" 			=> "GPS Processing Method",
		"exif:GPSAreaInformation" 			=> "GPS Area Information",
		"exif:GPSDifferential" 				=> "GPS Differential",
		// Exif Flash
		"exif:Fired" 						=> "Fired",
		"exif:Return" 						=> "Return",
		"exif:Mode" 						=> "Mode",
		"exif:Function" 					=> "Function",
		"exif:RedEyeMode" 					=> "Red Eye Mode",
		// Exif OECF/SFR
		"exif:Columns" 						=> "Columns",
		"exif:Rows" 						=> "Rows",
		"exif:Names" 						=> "Names",
		"exif:Values" 						=> "Values",
		"exif:Settings" 					=> "Settings",
		
		"stDim:w" 							=> "Width",
		"stDim:h" 							=> "Height",
		"stDim:unit" 						=> "Units",
		
		"xapGImg:height"	 				=> "Height",
		"xapGImg:width" 					=> "Width",
		"xapGImg:format" 					=> "Format",
		"xapGImg:image" 					=> "Image",
		
		"stEvt:action" 						=> "Action",
		"stEvt:instanceID" 					=> "Instance ID",
		"stEvt:parameters" 					=> "Parameters",
		"stEvt:softwareAgent" 				=> "Software Agent",
		"stEvt:when" 						=> "When",
		
		"stRef:instanceID" 					=> "Instance ID",
		"stRef:documentID" 					=> "Document ID",
		"stRef:versionID" 					=> "Version ID",
		"stRef:renditionClass" 				=> "Rendition Class",
		"stRef:renditionParams" 			=> "Rendition Parameters",
		"stRef:manager" 					=> "Asset Management System",
		"stRef:managerVariant" 				=> "Particular Variant of Asset Management System",
		"stRef:manageTo" 					=> "Manage To",
		"stRef:manageUI" 					=> "Managed Resource URI",
		
		"stVer:comments" 					=> "",
		"stVer:event" 						=> "",
		"stVer:modifyDate" 					=> "",
		"stVer:modifier" 					=> "",
		"stVer:version" 					=> "",
		
		"stJob:name" 						=> "Job Name",
		"stJob:id" 							=> "Unique Job ID",
		"stJob:url" 						=> "URL for External Job Management File",
		
		"photopress:camera"					=> "Camera"
				
		);
	}
		
}

function papt_getMetaData($id) {
	
	$md = new papt_photoTaxonomies();
	
	$mdata = wp_get_attachment_metadata( $id );
	
	if ( isset( $mdata['papt_meta'] ) ) {
		$md->loadFromArray( $mdata['papt_meta'] );
	} else {
		$file = get_attached_file($id);
		$md->loadFromFile($file);	
	}
	
	return $md;
}

function papt_getMetaDataFromFile($id) {
	$md = new papt_photoTaxonomies();
	$file = get_attached_file($id);
	$md->loadFromFile($file);
	return $md;	
	
}

function papt_addAttachment($id) {
	
	//extract metadata from file	
	$file = get_attached_file($id);
	$md = new papt_photoTaxonomies();
	$md->loadFromFile($file);
	
	papt_addAttachmentTags($id, $md);
	
	// set ALT text and caption of image
	$post = get_post( $id );
	
	// make this configurable at some point
	$alt = $post->post_title . ' by ' . $md->getCopyrightHolder() . '. ';
	
	if ( ! update_post_meta($id, '_wp_attachment_image_alt', $alt) ) {
		add_post_meta($id, '_wp_attachment_image_alt', $alt);
	}
}

function papt_addAttachmentTags($id, $md) {
	//print_r($md);
	// add keyword tags
	$keywords = $md->getKeywords();
	if (!empty($keywords)) {
		wp_set_object_terms($id, $keywords, 'photos_keywords', $append = false);
	}
	
	//add geo location tags
	$city = $md->getCity();
	if (!empty($city)) {
		wp_set_object_terms($id, $city, 'photos_city', $append = false);
	}
	
	$state = $md->getState();
	if (!empty($state)) {
		wp_set_object_terms($id, $state, 'photos_state', $append = false);
	}
	
	$country = $md->getCountry();
	if (!empty($country)) {
		wp_set_object_terms($id, $country, 'photos_country', $append = false);
	}

	//add camera tag
	$camera = $md->getCamera();
	if (!empty($camera)) {
		wp_set_object_terms($id, $camera, 'photos_camera', $append = false);
	}
	
	//add lens tag
	$lens = $md->getLens();
	if (!empty($lens)) {
		wp_set_object_terms($id, $lens, 'photos_lens', $append = false);
	}

}

function photopress_showExif( $atts ) {

	extract( shortcode_atts( array(

		'post_id' 			=> '',
		'keys'				=> 'exif:ExposureTime,exif:FNumber,exif:FocalLength,exif:ISOSpeedRatings,exif:DateTimeDigitized,photopress:camera'

	), $atts ) );
	
	$keys = explode(',', $keys);
	
	$meta = papt_getMetaData( $post_id );
	
	return $meta->render( $meta->get( $keys ) );
}

class papt_displayExif extends WP_Widget {
	
	function papt_displayExif() {
	
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'papt_displayExif', 'description' => "Display's the EXIF info of an image. Can only be used on single image or attachment pages." );

		/* Widget control settings. */
		$control_ops = array('width' => 300);
		
		/* Create the widget. */
		parent::WP_Widget('papt_displayExif', 'PhotoPress - Display Exif', $widget_ops, $control_ops);
	}
	
	function widget( $args, $instance ) {
		
		global $post;
		
		extract( $args );
		
		if ( ! $keys ) {
			
			$keys = 'exif:ExposureTime,exif:FNumber,exif:FocalLength,exif:ISOSpeedRatings,exif:DateTimeDigitized';
		}
		
		$keys = explode(',', $keys);
	 	
		/* User-selected settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		$meta = papt_getMetaData($post->ID);
		
		$html = '';
		
		$values = $meta->get( $keys );
		
		if ( $values ) {
		
			$html = $meta->render($values);
		}
		
		if ( $values && $html ) {
		
			echo "<h2>$title</h2>";		
			echo $html;
			/* After widget (defined by themes). */
			echo $after_widget;
		} else {
			
			echo '<!-- Widget had nothing to output. -->';
		}
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['keys'] = strip_tags( $new_instance['keys'] );

		return $instance;
	}
	
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Example' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label><BR>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'keys' ); ?>">Meta Data Keys (optional):</label><BR>
			<input id="<?php echo $this->get_field_id( 'keys' ); ?>" name="<?php echo $this->get_field_name( 'keys' ); ?>" value="<?php echo $instance['keys']; ?>" style="width:100%;" />
		</p>

		<?php
	}
}

class papt_displayTaxTerms extends WP_Widget {
	
	function papt_displayTaxTerms() {
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'papt_displayTaxTerms', 'description' => "Display's the taxonomy terms of an image. Can only be used on single image or attachment pages." );

		/* Widget control settings. */
		$control_ops = array();
		
		parent::WP_Widget('papt_displayTaxTerms', 'PhotoPress - Display Taxonomies', $widget_ops, $control_ops);
	}
	
	function widget( $args, $instance ) {
		
		global $post;
		
		extract( $args );
		
		/* User-selected settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		
		/* Before widget (defined by themes). */
		echo $before_widget;
		
		echo "<h2>$title</h2>";
		echo '<dl class="table-display">';	
		echo get_the_term_list( $post->ID, 'photos_keywords', '<DT>Keywords: </DT><DD>', ', ', '</DD>' );	
		echo get_the_term_list( $post->ID, 'photos_camera', '<DT>Camera: </DT><DD>', ', ', '</DD>' );
		echo get_the_term_list( $post->ID, 'photos_lens', '<DT>Lens: </DT><DD>', ', ', '</DD>' );
		echo get_the_term_list( $post->ID, 'photos_city', '<DT>City: </DT><DD>', ', ', '</DD>' );	
		echo get_the_term_list( $post->ID, 'photos_state', '<DT>State: </DT><DD>', ', ', '</DD>' );
		echo get_the_term_list( $post->ID, 'photos_country', '<DT>Country: </DT><DD>', ', ', '</DD>' );
		echo get_the_term_list( $post->ID, 'photos_people', '<DT>People: </DT><DD>', ', ', '</DD>' );
		echo '<dl>';
		/* After widget (defined by themes). */
		echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}
	
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Example' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<?php
	}
}


///////////////////////////////////////////////////////////////////////////////////
////
//// Wordpress Template Functions
////
///////////////////////////////////////////////////////////////////////////////////

/**
 * Displays the label for a specific Taxonomy
 *
 * @param	$taxonmy_name	string	the name of the taxonomy
 */
function papt_displayTaxonomyLabel($taxonomy_name = '') {
	
	if ( ! $taxonomy_name ) {
		
		$taxonomy_name = get_query_var( 'taxonomy' );
	}
	
	$t = get_taxonomy($taxonomy_name);
	//print_r($t);
	echo $t->label;
}

/**
 * Returns the label for a specific Taxonomy
 *
 * @param	$taxonmy_name	string	the name of the taxonomy
 * @depricated
 */
function papt_getTaxonomyLabel($taxonomy_name = '') {
	
	if ( ! $taxonomy_name ) {
		
		$taxonomy_name = get_query_var( 'taxonomy' );
	}
	
	$t = get_taxonomy($taxonomy_name);
	return $t->object_type;
}

/**
 * Displays a tag cloud for a specific taxonomy
 *
 * This functinon basically just a wrapper for wordpress wp_tag_cloud()
 *
 * @param	$taxonomy	string	the name of the taxonomy
 * @param	$format		string	the tag cloud format
 * @link	http://codex.wordpress.org/Function_Reference/wp_tag_cloud
 */
function papt_displayTaxonomyTagCloud( $args = array() ) {
	
	if ( ! isset( $args['taxonomy'] ) ) {
		
		$args['taxonomy'] = get_query_var( 'taxonomy' );
	}
	
	wp_tag_cloud( $args );
}

/**
 * Retrieves a list of posts by Taxonomy and Term
 *
 * @param	$taxonomy	string	name of the taxonomy.
 * @param	$term		string	the term
 * @param	$field		string 	the taxonomy field to query
 * @param	$num_posts	integer	the number of posts you want
 * @param	$post_type	string	the type of post you want
 */
function papt_getTaxonomyPosts($taxonomy = '', $term = '', $field = 'slug', $num_posts = 25, $post_type = 'attachment') {
	
	if ( ! $taxonomy ) {
		$taxonomy = get_query_var( 'taxonomy' );	
	}
	
	if ( ! $field ) {
		$field = 'slug';
	}
	
	if ( ! $term ) {
		
		$term = get_query_var( 'term' );
	}
	
	// normal paging
	$paged = (get_query_var('paged')) ? (int) get_query_var('paged') : '';
	
	// needed for use on taxonomy.php for some reason
	if ( ! $paged ) {
		
		$paged = (get_query_var('page')) ? (int) get_query_var('page') : 1;	
	}
	
	$args = array(
		'tax_query' => array(),
		'showposts' 		=> $num_posts,
		//'posts_per_page' 	=> $num_posts,
		'post_type' 		=> $post_type,
		'paged' 			=> $paged,
		'post_status'		=>'all'
		//'no_found_rows' => true
		
	);
	
	$taxes = array();
	
	if ( ! is_array($taxonomy) ) {
	
		$taxes[] = $taxonomy;
	} else {
		$taxes = $taxonomy;
	}

	foreach ( $taxes as $tax) {
	
		$args['tax_query'][] = array(
									'taxonomy' => $tax,
									'field' => $field,
									'terms' => array($term),
									'operator' => 'IN'
		);
	}
	
	// set realtion if more than one taxonomy
	if ( isset( $taxes[1] ) ) {
	
		$args['tax_query']['relation'] = 'OR';
	}
	//print_r($args);
	
	query_posts( $args );
	
	
	
	//return new WP_Query( $args );
	
}

///////////////////////////////////////////////////////////////////////////////////
////
//// Wordpress Hook Handler Functions
////
///////////////////////////////////////////////////////////////////////////////////

/**
 * Registers PhotoTools custom Taxonomies
 */
function papt_regtax() {
		
	register_taxonomy('photos_camera', 'attachment', array(
							'hierarchical' => false, 
							'label' => __('Cameras', 'series'),
							'query_var' => 'photos_camera', 
							'rewrite' => false,
							'update_count_callback'	=> '_update_generic_term_count',
							'show_admin_column' => true,
							'public'	=> true ));
							
	register_taxonomy('photos_lens', 'attachment', array( 
							'hierarchical' => false, 
							'label' => __('Lenses', 'series'), 
							'query_var' => 'photos_lens', 
							'rewrite' => false,
							'update_count_callback'	=> '_update_generic_term_count',
							'show_admin_column' => true,
							'public'	=> true ));
							
	register_taxonomy( 'photos_city', 'attachment', array(
							'hierarchical' => false, 
							'label' => __('Photo Cities', 'series'), 
							'query_var' => 'photos_city', 
							'rewrite' => false,
							'update_count_callback'	=> '_update_generic_term_count',
							'show_admin_column' => true,
							'public'	=> true ));
							
	register_taxonomy( 'photos_state', 'attachment', array(
							'hierarchical' => false, 
							'label' => __('Photo States', 'series'), 
							'query_var' => 'photos_state', 
							'rewrite' => false,
							'update_count_callback'	=> '_update_generic_term_count',
							'show_admin_column' => true,
							'public'	=> true ));
							
	register_taxonomy( 'photos_country', 'attachment', array(
							'hierarchical' => false, 
							'label' => __('Photo Countries', 'series'), 
							'query_var' => 'photos_country', 
							'rewrite' => false,
							'update_count_callback'	=> '_update_generic_term_count',
							'show_admin_column' => true,
							'public'	=> true ));
							
	register_taxonomy( 'photos_people', 'attachment', array(
							'hierarchical' => false, 
							'label' => __('Photo People', 'series'), 
							'query_var' => 'photos_people', 
							'rewrite' => false,
							'update_count_callback'	=> '_update_generic_term_count',
							'show_admin_column' => true,
							'public'	=> true ));
							
	register_taxonomy('photos_keywords', 'attachment', array( 
					   		'hierarchical' => false, 
							'label' => __('Photo Keywords', 'series'), 
							'query_var' => 'photos_keywords', 
							'rewrite' => false,
							'update_count_callback'	=> '_update_generic_term_count',
							'public'	=> true));
	
	register_taxonomy( 'photos_collection', 'attachment', array(
							'hierarchical' => false, 
							'label' => __('Photo Collections', 'series'), 
							'query_var' => 'photos_collection', 
							'rewrite' => false,
							'update_count_callback'	=> '_update_generic_term_count',
							'show_admin_column' => true,
							'public'	=> true ));
							
	register_taxonomy( 'photos_prints', 'attachment', array(
							'hierarchical' => false, 
							'label' => __('Photo Print Sizes', 'series'), 
							'query_var' => 'photos_prints', 
							'rewrite' => false,
							'update_count_callback'	=> '_update_generic_term_count',
							'show_admin_column' => true,
							'public'	=> true ));
}

/**
 * Stores addtional PhotoTools meta data as part of the Post's meta field.
 *
 * @param 	$data	array	the post's meta data array
 * @param	$id	integer	the post's ID.
 */
function papt_storeNewMeta($data, $id) {
	
	$md = new papt_photoTaxonomies();
	$file = get_attached_file($id);
	$md->loadFromFile($file);
	$data['papt_meta'] = $md->getAllMetaData();
	//print_r($data['papt_meta']);
	papt_addAttachmentTags($id, $md);

	return $data;
}

/** 
 * Populates ALT text and excerpt fields on image attachment form 
 * 
 * @param array $form_fields 
 * @param object $post 
 * @return array 
 */  
function papt_attachment_meta_form_fields( $form_fields, $post ) {  
  
    return $form_fields;  
} 

/**
 * Registers Wordpress widgets 
 */
function papt_load_widgets() {

	register_widget( 'papt_displayExif' );
	register_widget( 'papt_displayTaxTerms' );	
}


/**
 * Sets proper post_status in default loop so that it picks
 * up attachment posts for PhotoTools Taxonomies (which
 * are always full of attachments).
 *
 * @param $query	WP_Query Object
 *
 */
function papt_makeTaxonomiesVisibleToLoop($query) {
    
    
    if (is_tax('photos_keywords') || 
    	is_tax('photos_people') || 
    	is_tax('photos_city') || 
    	is_tax('photos_state') ||
    	is_tax('photos_country') ||
    	is_tax('photos_camera') ||
    	is_tax('photos_lens') ||
    	is_tax('photos_collection')
    
    ) {
    	$query->set('post_status','inherit');   
    }
	
	return $query;
}

function papt_makeAttachmentsVisibleInTaxQueries( $query ) {

	if ( is_tax() ) {
	
		$query->set( 'post_status', 'all' );
	}
	
	return $query;
}



///////////////////////////////////////////////////////////////////////////////////
////
//// Wordpress Hooks & Registrations
////
///////////////////////////////////////////////////////////////////////////////////

/**
 * Action handler for when new images are uploaded
 */
add_action('add_attachment', 'papt_addAttachment');

/**
 * Action handler for when Images are edited from the attachment page.
 */
//add_action('edit_attachment', 'papt_editAttachment');

/**
 * Handler for extracting meta data from image file and storing it as
 * part of the Post's meta data.
 */
add_filter('wp_generate_attachment_metadata', 'papt_storeNewMeta',1,2);
// is this really needed if all we are doing in pulling the metadata from the file again.
add_filter('wp_update_attachment_metadata', 'papt_storeNewMeta',1,2);

/**
 * Registers the photo taxonomies
 */
add_action('init', 'papt_regtax');

/**
 * Register's Widgets 
 */
add_action( 'widgets_init', 'papt_load_widgets' );

/**
 * Adds non taxonomy fields on the Attachment Edit form.
 */
//add_filter("attachment_fields_to_edit", "papt_attachment_meta_form_fields", 11, 2);

/**
 * Registers the papt image page sidebar
 */
register_sidebar(array(
  'name' => 'PhotoPress Image Page Sidebar',
  'id' => 'papt-image-sidebar',
  'description' => 'Widgets in this area will be shown on image (attachment) page templates.',
  'after_widget' => '<BR>'
));

// needed to show attachments on taxonomy pages
add_filter( 'pre_get_posts', 'papt_makeAttachmentsVisibleInTaxQueries' );
// needed to make attachment posts visible in the default loop.
//add_filter('pre_get_posts', 'papt_makeTaxonomiesVisibleToLoop');
add_shortcode('photopress-exif', 'photopress_showExif');

?>