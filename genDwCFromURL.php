#!/usr/bin/php
<?php


/**
 * list of fields required within the DwC images CSV
 * copied from Symbiota/trunk/classes/OccurrenceDwcArchiver.php
 */
$imageFieldArr = array(
	'coreid' => '',
	'accessURI' => 'http://rs.tdwg.org/ac/terms/accessURI',		//url 
	'providerManagedID' => 'http://rs.tdwg.org/ac/terms/providerManagedID',	//GUID
	'title' => 'http://purl.org/dc/terms/title',	//scientific name
	'comments' => 'http://rs.tdwg.org/ac/terms/comments',	//General notes	
	'Owner' => 'http://ns.adobe.com/xap/1.0/rights/Owner',	//Institution name
	'rights' => 'http://purl.org/dc/terms/rights',		//Copyright unknown
	'UsageTerms' => 'http://ns.adobe.com/xap/1.0/rights/UsageTerms',	//Creative Commons BY-SA 3.0 license
	'WebStatement' => 'http://ns.adobe.com/xap/1.0/rights/WebStatement',	//http://creativecommons.org/licenses/by-nc-sa/3.0/us/
	'MetadataDate' => 'http://ns.adobe.com/xap/1.0/MetadataDate',	//timestamp
	'associatedSpecimenReference' => 'http://rs.tdwg.org/ac/terms/associatedSpecimenReference',	//reference url in portal
	'type' => 'http://purl.org/dc/terms/type',		//StillImage
	'subtype' => 'http://rs.tdwg.org/ac/terms/subtype',		//Photograph
	'format' => 'http://purl.org/dc/terms/format',		//jpg
	'metadataLanguage' => 'http://rs.tdwg.org/ac/terms/metadataLanguage'	//en
	);














// validate input args..
if (count($argv) < 3) {
	print "\n";
	print "Description:\n";
	print "  Generate the DwC from a base URL.\n";
	print "    - images.csv\n";
	print "    - occurrence.csv\n";
	print "\n";
	print "Usage: \n";
	print "  genDwCFromURL <url> <name>\n";
	print "\n";
	print "    url: base url, which has inputs, ocr and parsed subdirs.\n";
	print "\n";
	print "Example:";
	print "  genDwCFromURL http://aocr1.acis.ufl.edu/datasets/ent/gold ent\n";
	print "\n";
	exit();
} else {
	$inputURL = $argv[1];
	$name = $argv[2];
}









/**
 * INITIALIZE
 */
// big assumption for simplicity:
//   -> a single image per occurance...
$images = imagesFromURL($inputURL . '/inputs/');
//displayAccessURIs($images);
$parsed = array();












/**
 * PARSE
 */


// iterate over each occurrence (i.e. image)
for ($i = 0; $i < count($images); $i++) {
	$image = $images[$i];
	print "  [occid:" . $image['occid'] . "]\n";
  print "    image: " . $image['accessURI'] . "\n";
	$ocr_text = readOCRForImage($images[$i]['occid'], $inputURL . '/ocr/' . $images[$i]['occid'] . '.txt');
	$parsedForImage = readParsedForImage($images[$i]['occid'], $inputURL . '/parsed/' . $images[$i]['occid'] . '.csv');
	$parsed = array_merge($parsed, $parsedForImage);
}







/** 
 * OUTPUT
 */
writeOccurrenceFile($name . '-occur.csv', $parsed);
writeImageFile($name . '-images.csv', $images, array_keys($imageFieldArr));
// writeDetFile
// writeEMLFile
// writeMetaFile




































/**
 * find and read the raw ocr results for this occid
 */
function readOCRForImage($occid, $ocrFileURL) {
	$ocr_text = @file_get_contents($ocrFileURL);

	if (strlen($ocr_text) > 0) {
		print "    processed ocr text (" . strlen($ocr_text) . " chars)\n";
	} else {
		print "    ocr text not found\n";
	}
	return $ocr_text;
}



/**
 * find and read the parsed/processed results from the ocr step
 * insert 'dwc:occid' to each row
 */
function readParsedForImage($occid, $parsedFileURL) {
	$parsed = array();
	
	if (($fh = fopen($parsedFileURL,'r')) !== FALSE) {
		$lineNum = 0;
		while (($line = fgetcsv($fh)) !== FALSE) {
			if ($lineNum == 0) {
			
				// insert occid to the beginning of the header row	
				$line = array_merge(array("dwc:occid"), $line);
				$header = $line;
				
				
			} else {
				$aline = array();
				foreach ($line as $k => $value) {
					$aline[$i][$k]  = 
				}
				// insert occid to the beginning of the array
				$line = array_merge(array($occid), $line);
//				print_r($line);
				array_push($parsed, $line);
			}
			$lineNum++;
		}
		
		print "    processed ocr parsed text (" . $lineNum . " lines)\n";
	} else {
		print "    parsed ocr text not found\n";
	}
	fclose($fh);
	return $parsed;
}






/**
 * write an -image.csv file from a set of images
 */
function writeImageFile($filePath, $images, $headers) {
	print "writing image file: " . $filePath . "\n";
	print "  image count: " . count($images) . "\n";
	$fh = fopen($filePath,"w");

	// write the header line..
	fputcsv($fh, $headers);



	// print each line
	for ($i = 0; $i < count($images); $i++) {
		fputcsv($fh, createImageLine($images[$i]));
		//print createImageLine($images[$i]) . "\n";
	}

	fclose($fh);
}


/**
 * write an image.csv file from a 'gold/silver' dataset
 */
function writeOccurrenceFile($filePath, $nameValues) {
	print "writing occcurrence file: " . $filePath . "\n";
	print "  occurrence count: " . count($nameValues) . "\n";
	$fh = fopen($filePath,"w");

	if (count($nameValues) > 0) {

		// write the header line..
		fputcsv($fh, array_keys($nameValues[0]));
//print_r(array_keys($nameValues[0]));
//print_r($nameValues[0]);
//print_r(array_values($nameValues[0]));
		

		// print each line
		for ($i = 0; $i < count($nameValues); $i++) {
			fputcsv($fh, array_values($nameValues[$i]));
		}
	} else {
		print "  no values to write for occurrence file";
	}

	fclose($fh);
}







/**
 * Create an image object (associative array), order is important.
 * Copied/updated to be generalized from symbiota OccurrenceDwcArchiver.php
 *
 * name/value pairs
 * occid (occurence id)
 * accessURI (accesss uri)
 * providermanagedid (provider managed id)
 * title (title/sciname)
 * comments (caption/notes)
 * owner
 * rights
 * usageterms (useage terms)
 * webstatement (access rights)
 * metadatadate (initial time stamp)
 * associatedSpecimenReference
 * type
 * subtype
 * format
 * metadataLanguage
 */
function createImageLine($r) {
	$line = array();
	$server = "localhost";
	$clientRoot = "/client-root";
	$referencePrefix = 'http://'.$server.'/';


	// occid
	if (array_key_exists('occid',$r)) {
		$line['occid'] = $r['occid'];

	} else {
		// otherwise, use the filename if accessURI exists
		if (array_key_exists('accessURI',$r)) {
			$url = parse_url($r['accessURI']);
			$pinfo = pathinfo($url['path']);
			$line['occid'] = $pinfo['filename'];

		// otherwise, use -1
		} else {
			$line['occid'] = '-1';
		}
	}


	// accessURI
	if (array_key_exists('accessURI',$r)) {
		if(substr($r['accessURI'],0,1) == '/') {
			$r['accessURI'] = $referencePrefix.$r['accessURI'];
		} else {
			$line['accessURI'] = $r['accessURI'];
		}
	} else {
		$line['accessURI'] = '';
	}


	// providermanagedid
	if (array_key_exists('providermanagedid',$r)) {
		$line['providermanagedid'] = $r['providermanagedid'];
	} else {
		$line['providermanagedid'] = '';
	}


	// title
	if (array_key_exists('title',$r)) {
		$line['title'] = $r['title'];
	} else {
		$line['title'] = '';
	}

	// comments
	if (array_key_exists('comments',$r)) {
		$line['comments'] = $r['comments'];
	} else {
		$line['comments'] = '';
	}

	// owner
	if (array_key_exists('owner',$r)) {
		$line['owner'] = $r['owner'];
	} else {
		$line['owner'] = '';
	}

	// rights
	if (array_key_exists('rights',$r)) {
		$line['rights'] = $r['rights'];
	} else {
		$line['rights'] = '';
	}


	// usageterms (useage terms)
	if(array_key_exists('rights', $r)) {
		if(stripos($r['rights'],'http://creativecommons.org') === 0){
			$line['providermanagedid'] = 'urn:uuid:'.$_SERVER["SERVER_NAME"].':'.$r['providermanagedid'];
			$line['webstatement'] = $r['rights'];
			$line['rights'] = '';
			if(array_key_exists('usageterms',$r)){
				if($r['webstatement'] == 'http://creativecommons.org/publicdomain/zero/1.0/'){
					$line['usageterms'] = 'CC0 1.0 (Public-domain)';
				}
				elseif($r['webstatement'] == 'http://creativecommons.org/licenses/by/3.0/'){
					$line['usageterms'] = 'CC BY (Attribution)';
				}
				elseif($r['webstatement'] == 'http://creativecommons.org/licenses/by-sa/3.0/'){
					$line['usageterms'] = 'CC BY-SA (Attribution-ShareAlike)';
				}
				elseif($r['webstatement'] == 'http://creativecommons.org/licenses/by-nc/3.0/'){
					$line['usageterms'] = 'CC BY-NC (Attribution-Non-Commercial)';
				}
				elseif($r['webstatement'] == 'http://creativecommons.org/licenses/by-nc-sa/3.0/'){
					$line['usageterms'] = 'CC BY-NC-SA (Attribution-NonCommercial-ShareAlike)';
				}
			}
		}
	}

	// if the useageterms still aren't defined..
	if (!array_key_exists('usageterms',$line)) {
		$line['usageterms'] = 'CC BY-NC-SA (Attribution-NonCommercial-ShareAlike)';
	}

	// webstatement (access rights)
	if (array_key_exists('webstatement',$r)) {
		$line['webstatement'] = $r['webstatement'];
	} else {
		$line['webstatement'] = '';
	}


	// metadatadate (initial time stamp)
	if (array_key_exists('metadatadate',$r)) {
		$line['metadatadate'] = $r['metadatadate'];
	} else {
		$line['metadatadate'] = '';
	}


	// associatedSpecimenReference
	//$line['associatedSpecimenReference'] = 'http://'.$server.$clientRoot.'/collections/individual/index.php?occid='.$line['occid'];
	$line['associatedSpecimenReference'] = '';

	$line['type'] = 'StillImage';
	$line['subtype'] = 'Photograph';

	// determine the image format
	$extStr = strtolower(substr($r['accessURI'],strrpos($r['accessURI'],'.')+1));
	if($extStr == 'jpg' || $extStr == 'jpeg'){
		$line['format'] = 'image/jpeg';
	}
	elseif($extStr == 'gif'){
		$line['format'] = 'image/gif';
	}
	elseif($extStr == 'png'){
		$line['format'] = 'image/png';
	}
	elseif($extStr == 'tiff' || $extStr == 'tif'){
		$line['format'] = 'image/tiff';
	}
	else{
		$line['format'] = '';
	}
	$line['metadataLanguage'] = 'en';

	return $line;
}






/**
 * from a base url, find all images that exist under it
 * create named arrays with accessURIs within it
 */
function imagesFromURL($baseURL) {
	$images = array();

	$html = file_get_contents($baseURL);
	print "opened url: " . $baseURL . ", size: " . strlen($html) . "\n";
	$count = preg_match_all('/<td><a href="([^"]+)">[^<]*<\/a><\/td>/i', $html, $files);
	print "  found " . (count($files[1])-1) . " (" . ($count-1) . ") files\n";

	// make sure it found some files..
	if (count($files) > 1) {
		// extract the individual files found
		for ($i = 1; $i < count($files[1]); ++$i) {
			$image = array();
			$url = $baseURL . $files[1][$i];
			$image['accessURI'] = $url;

			// parse the url	
			$url_parsed = parse_url($url);
			$pinfo = pathinfo($url_parsed['path']);
			$image['occid'] = $pinfo['filename'];

			array_push($images, $image);
		}
	}

	return $images;
}




// debugging function to pring out all urls found..
function displayAccessURIs($images) {
	for ($i = 0; $i < count($images); $i++) {
		print $images[$i]['accessURI'] . "\n";
	}
}




?>
