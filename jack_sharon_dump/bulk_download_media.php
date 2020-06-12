 <?php
ini_set('display_errors',1); 
error_reporting(E_ALL);	
ini_set("memory_limit","128M");	  // should be plenty, because we are using WGet to download the media, not PHP
$file_tmp = "ids_for_media_download.txt";     // this file should just list entryId for the asset, no comma, one on each line. Output files are named after the entryId and flavor
$save_loc = "output/";  // folder below this script where the media files will be saved - edit as needed for drive space. Full path would be e.g. "D://temp/media/" 

$flavorParamId = 487091; // 487091 suggested for highest qual, but may not exist. Kaltura will default to 487041 base flavor and send that instead, so beware of files without your top qual
						 // I suggest you sort your media in the CSV by available quality (see columns on right with byte sizes listed) and download batches with the appropriate
						 // highest quality for that subset. That way you get the highest quality mp4 for each entry.
						 // Note on Source flavor:
						 // To download source media, use 0 instead of 487091
                         // the issue with downloading source media is the extension. This code, without doing a special API flavor query, will not know the source file extension, 
						 // and .mp4 is likely not going to be correct for a small subset of source files. The older the more likely they are not mp4. 
						 // You can extract that info from the API, but now you have to write elaborate code to create the accurate URL here. You get the media here, but you may
						 // have to rename files that don't play to e.g. .mov or .wmv, .flv or whatever may work. 
						 //
						 // output file name will reflect the flavor so you can run this several times and grab all flavors without overwriting output.

if($flavorParamId == 0){$flavor_name = "source";
	} else {
	$flavor_name = $flavorParamId;}


$parts = new SplFileObject($file_tmp);
foreach($parts as $line) {
    $url = trim($line);
	$url = 'https://api.kaltura.nordu.net/p/1660902/sp/0/playManifest/entryId/' . $url . '/format/url/flavorParamId/' . $flavorParamId . '/' . $url . '_' . $flavor_name . '.mp4';	

    if (!$url) {
        continue;
    }
	
	echo "downloading " . $url . "\r\n";
    $dir = "{$save_loc}".basename($url);

	// For Windows use
	// 	exec("wget.exe --no-verbose -N --no-use-server-timestamps --append-output=wget_log.txt -O $dir $url"); // change --no-verbose to -v for verbose logging. Caution, with -v the log can get huge in big jobs
	// For macOS/Linux use
	// 	exec("wget --no-verbose -N --no-use-server-timestamps --append-output=wget_log.txt -O $dir $url"); // change --no-verbose to -v for verbose logging. Caution, with -v the log can get huge in big jobs
	exec("wget --no-verbose -N --no-use-server-timestamps --append-output=wget_log.txt -O $dir $url"); // change --no-verbose to -v for verbose logging. Caution, with -v the log can get huge in big jobs

    continue;
}

echo "done \r\n";


?>

