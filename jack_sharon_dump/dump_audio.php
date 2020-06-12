<?php

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'php5'.DIRECTORY_SEPARATOR.'KalturaClient.php');

// Script will run for a maximum of 24 hours.
set_time_limit(86400);

// Script will consume a maximum of 5GB of memory.
ini_set( 'memory_limit' , '5120M' );

error_reporting(E_ERROR | E_WARNING | E_PARSE);



/*
Original script by Jack Sharon (dropbox download), edited by Peter Burke 6/5/2020
script modified from Jack's original script to return captions id field rather than language to facilitate downloading
also cahnged msDuration to Duration and added metadata fields 
Views, width, height, co-Editors, co-Publishers

Width and height kept in audio output so it can be merged with video dump CSV
*/



//Load config details from INI file
$config = parse_ini_file(dirname(__file__) . DIRECTORY_SEPARATOR . 'config.ini');

date_default_timezone_set($config['timezone']); //set the expected timezone

$partner = $config['PARTNER'];
$partnerId = $config['PID'];
$secret = $config['admin_secret'];
$outputdir = $config['output_folder'];

$userId = null;
$expiry = null;
$privileges = 'disableentitlement';
$type = KalturaSessionType::ADMIN;
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = 'https://api.kaltura.nordu.net';
$client = new KalturaClient($config);
$ks = $client->session->start($secret, $userId, $type, $partnerId, $expiry, $privileges);
$client->setKs($ks);
$mediatype = audio;

//CSV filename
$csv_output_file = $outputdir . $partner.'_' . $partnerId.'_dump_' . $mediatype.'.csv';

//Clear output file if it already exists
$f = @fopen($csv_output_file, "r+");
if ($f !== false) {
    ftruncate($f, 0);
    fclose($f);
}

echo date('r') . ": Starting for PID " . $partnerId. " " . $mediatype. " files\n";

//Get array of all entries that match filter criteria
$allEntriesArray = getFullListOfEntries($client);

//Get definitions for all video-related flavor params available in this PID
$flavorParamsArray = getFlavorParams($client);

//Get all categories in this PID
$allCategoriesArray = getCategories($client);

//Array which will contain details (name, email) of an entry owner. We add user data to it dynamically as we process the entries
$userDataArray = array();

$totalCount = count($allEntriesArray);

echo("\n");
echo date('r') . ": Getting and processing flavors for " . $totalCount . " entries\n";


//Declare variable to contain header fields. 
$headers = 'Entry ID,Entry Name,UserId,Email,Name,Tags,Duration,Status,Created At,Updated At,Plays,Views,width,height,Last Played,co-Editors,co-Publishers,CaptionsID,Category IDs,Category Names';

//Add flavors params to header
foreach ($flavorParamsArray as $fp_id => $fp_val) {
	$headers .= ',' . $fp_val . ' -- ' . $fp_id . ' Size (KB)';
}

//Output CSV column headers
error_log($headers . "\n",3,$csv_output_file);


$c = 1; //counter
foreach ($allEntriesArray as $entry) {
	$output = '';
	$entry_name = str_replace("'", "", $entry->name); // remove single quotes from entry names
	$entry_name = str_replace(",", "", $entry_name); // remove commas from entry names
	$entry_name = str_replace('"', "", $entry_name); // remove commas from entry names

	$flav = array(); //array for values returned from flavor API call
	$flav = getFlavorData($entry->id, $client); //get flavors data for current entry.

	$cap = array();
	$cap = getCaptionData($entry->id, $client); //get list of captions languages for current entry.

	$cat = array();
	$cat = getCategoryData($entry->id, $client); //get list of category IDs for current entry.

	
	$catNameArray = array();
	foreach ($cat as $k) {
		array_push($catNameArray, $allCategoriesArray[$k]);
	}

	//Get user details (email and name)
	if (isset($userDataArray[$entry->userId])){
		$x = $userDataArray[$entry->userId];
	} else {
		$x = getUser($entry->userId, $client);
		$userDataArray[$entry->userId] = $x;
	}
	
    $output .= $entry->id . ',' 
    	 	. $entry_name  . ','
    	 	. $entry->userId . ',' 
    	 	. $userDataArray[$entry->userId]->email . ',' 
    	 	. $userDataArray[$entry->userId]->fullName . ','
            . '"' . $entry->tags . '",'
    	 	. $entry->duration . ','
    	 	. $entry->status . ',"'   
    	 	. formatDate($entry->createdAt) . '","' 
    	 	. formatDate($entry->updatedAt) . '",' 
    	 	. $entry->plays . ','    	 	
    	 	. $entry->views . ','   
    	 	. $entry->width . ','   
    	 	. $entry->height . ',"'    
    		. checkLastPlayedDate($entry->lastPlayedAt) . '",'
            . '"' . $entry->entitledUsersEdit . '",'
            . '"' . $entry->entitledUsersPublish . '",'			
    		. '"' . implode(",", $cap) . '",'  
    		. '"' . implode(";", $cat) . '",' //Using semi-colons as separator bc when using comma's Excel seems to mess up the fields (thinking they are numbers)
    		. '"' . implode(",", $catNameArray) . '",'; 
			

    //Loop over the flavor params array and check if there is a corresponding flavor asset in this entry's flavors array 
    foreach ($flavorParamsArray as $fp_id => $fp_val) {
    	if (array_key_exists($fp_id,$flav)){
    		$output .= $flav[$fp_id]; 
    	}
    	$output .= ',' ;
    }

    $output .=  "\n";

	//Append to CSV file
	file_put_contents($csv_output_file, $output, FILE_APPEND);

	printProgress("Processing " . $c . " of " .$totalCount);
	$c++;
}

echo "\n";
echo date('r') . ": Total unique entry owners (users): " . count($userDataArray) . "\n";
echo date('r') . ": End processing\n";
echo date('r') . ": Data saved to $csv_output_file\n";
exit();


/*
* Check date
*/

function checkLastPlayedDate($d)
{
	$x = 0;
	if ($d > 0) {
		$x = date('Y-M-d', $d);
	}
	return $x;
}


/*
* Format date
*/

function formatDate($d)
{
	//2017-Nov-29, 03:19am
	return date('Y-M-d, h:ia', $d);
}

/*
* Print progress, overwrite line each time
*/

function printProgress($x)
{
	echo "\r\033[0K";
	echo $x;
	flush();
}


/*
* Return user object.
* 
* @param string $userId 
* @param API client object
*/

function getUser($userId, $client)
{
	try {
		$result = $client->user->get($userId);
	} catch (Exception $e) {
		echo $e->getMessage();
	}
	return $result;
}




/*
* Return array of category IDs, if any, for an entry.
* 
* @param string $entryId 
* @param API client object
*/

function getCategoryData($entryId, $client)
{
	$r = array();
	$category_filter = new KalturaCategoryEntryFilter();
	$category_filter->entryIdEqual = $entryId;
	$pager = new KalturaFilterPager();
  	$pager->pageSize = 500;
  	$pager->pageIndex = 1;
	
	try {
		$category_result = $client->categoryEntry->listAction($category_filter, $pager);
	} catch (Exception $err) {
		return $r;
	}

	foreach ($category_result->objects as $category_data){
		array_push($r, $category_data->categoryId);
	}

	return $r;
}


/*
*  Return array of all category fullNames, indexed by CategoryID.
* 
*  @param API client object
*/


function getCategories($client)
{
	$allCategories = array ();
	$pager = new KalturaFilterPager ();
	$pager->pageSize = 500;

	$filter = new KalturaCategoryFilter();
	$filter->orderBy = "-createdAt";

	$lastCreatedAt = 0;
	$lastCategoryIds = "";

	$dotsArray = array (); // For display feedback only

	$cont = true;
	while ($cont) {
		//Ignores categories that have already been parsed
		if ($lastCreatedAt != 0)
			$filter->createdAtLessThanOrEqual = $lastCreatedAt;
		if ($lastCategoryIds != "")
			$filter->idNotIn = $lastCategoryIds;

		// not overload the server
		sleep(1);
		$results = $client->category->listAction ( $filter, $pager );

		//If no categories are retrieved then the loop ends

		if (count ( $results->objects ) == 0)
		{
			$cont = false;
		}
		else
		{
			foreach ( $results->objects as $category )
			{

				$allCategories[$category->id] = str_replace('>', '&gt;', $category->fullName);
			
				//update the last lastCreatedAt and lastCategoryIds to exclude already returned entries in next list.
				if ($lastCreatedAt != $category->createdAt)
					$lastCategoryIds = "";

				if ($lastCategoryIds != "")
					$lastCategoryIds .= ",";

				$lastCategoryIds .= $category->id;
				$lastCreatedAt = $category->createdAt;
			}
		}
		printProgress("Building list of categories " . implode("", $dotsArray));
		array_push($dotsArray, ".");
		if (count($dotsArray) > 5) {
			$dotsArray = array();
			array_push($dotsArray, "(" . (string) count($allCategories) . " categories fetched so far)");
		}
	}

	echo PHP_EOL . (string) count ($allCategories) . " categories loaded." . PHP_EOL;

	return $allCategories;
}


/*
* Return arrayof caption languages, if any, for an entry.
* 
* @param string $entryId 
* @param API client object
*/

function getCaptionData($entryId, $client)
{
	$r = array();
	$caption_filter = new KalturaAssetFilter();
	$caption_filter->entryIdEqual = $entryId;
	$captionPlugin = KalturaCaptionClientPlugin::get($client);
	
	try {
		$caption_result = $captionPlugin->captionAsset->listAction($caption_filter, null);
	} catch (Exception $err) {
		return $r;
	}

	foreach ($caption_result->objects as $caption_data){
		if (isset($caption_data->id)) {
			array_push($r, $caption_data->id);
		}
	}
	return $r;
}

/*

* Return array of flavors for an entry, indexed by the flavorparamID.
* 
* @param string $entryId 
* @param API client object
*/

function getFlavorData($entryId, $client)
{
	$r = array();
	$asset_filter = new KalturaAssetFilter();
	$asset_filter->entryIdEqual=$entryId;
	try {
		$flavor_result = $client->flavorAsset->listAction($asset_filter, null);
	} catch (Exception $err) {
		return $r;
	}
	foreach ($flavor_result->objects as $flavor_data){
		if ($flavor_data->size > 0) {
			$r[$flavor_data->flavorParamsId] = $flavor_data->size;
		}
	}
	return $r;
}

/*

* Return array of video-related flavorParams for PID.
* 
* @param string $entryId 
* @param API client object
*/

function getFlavorParams($client)
{
  	$filter = new KalturaFlavorParamsFilter();
  	$pager = new KalturaFilterPager();
	$pager->pageSize = 100;
	$pager->pageIndex = 1;
  	$r = array();

  	try {
    	$result = $client->flavorParams->listAction($filter, $pager);
  	} catch (Exception $e) {
    	echo $e->getMessage();
    	return $r;
  	}

  	foreach ($result->objects as $param_data){
  		//Ignore everything except the source and flavors that have a video bitrate defined
  		if($param_data->id == 0 || $param_data->videoBitrate > 0) {
  			$r[$param_data->id] = $param_data->name;
  		}

  	}
	return $r;
}

/*

* Return array of all entries per filter defined in the function.
* 
* @param API client object
*/

function getFullListOfEntries($client) {
	$allEntries = array ();
	$pager = new KalturaFilterPager ();
	$pager->pageSize = 500;
	$lastCreatedAt = 0;
	$lastEntryIds = "";

	$filter = new KalturaMediaEntryFilter();
	$filter->orderBy = "-createdAt";
	$filter->mediaTypeEqual = KalturaMediaType::AUDIO;
	$filter->typeEqual = KalturaEntryType::MEDIA_CLIP;

	$dotsArray = array (); // For display feedback only

	$cont = true;
	while ($cont) {
		//Ignores entries that have already been parsed
		if ($lastCreatedAt != 0)
			$filter->createdAtLessThanOrEqual = $lastCreatedAt;
		if ($lastEntryIds != "")
			$filter->idNotIn = $lastEntryIds;

		// not overload the server
		sleep(1);
		$results = $client->media->listAction ( $filter, $pager );

		//If no entries are retrieved then the loop ends

		if (count ( $results->objects ) == 0)
		{
			$cont = false;
		}
		else
		{
			foreach ( $results->objects as $entry )
			{

				$allEntries[$entry->id] = $entry;
			
				//update the last lastCreatedAt and lastEntryIds to exclude already returned entries in next list.
				if ($lastCreatedAt != $entry->createdAt)
					$lastEntryIds = "";

				if ($lastEntryIds != "")
					$lastEntryIds .= ",";

				$lastEntryIds .= $entry->id;
				$lastCreatedAt = $entry->createdAt;
			}
		}
		printProgress("Building list of entries " . implode("", $dotsArray));
		array_push($dotsArray, ".");
		if (count($dotsArray) > 5) {
			$dotsArray = array();
			array_push($dotsArray, "(" . (string) count($allEntries) . " entries fetched so far)");
		}
	}

	echo PHP_EOL . (string) count ($allEntries) . " entries loaded." . PHP_EOL;

	return $allEntries;
}

?>
