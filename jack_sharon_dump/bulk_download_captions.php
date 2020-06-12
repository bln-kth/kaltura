 <?php
ini_set('display_errors',1); 
error_reporting(E_ALL);	
ini_set("memory_limit","128M");	  // set for largest file to be expected - no issue here with captions and wget in use.
$file_tmp = "ids_for_captions_download.txt";  // that is where you place each caption id on its own line (replace commas with line feed) to export.
$save_loc = "output/";  // folder below this script where the SRT files will be saved.

$parts = new SplFileObject($file_tmp);

foreach($parts as $line) {
	$url = trim($line);
	$url = 'https://api.kaltura.nordu.net/api_v3/service/caption_captionasset/action/serve/captionAssetId/' . $url;
	
	if (!$url) {
		continue;
	}
	echo "downloading " . $url . "\r\n";

	$dir = "{$save_loc}" . trim($line) . ".srt";
	echo "saving " . $dir . "\r\n\n\n";
	
	// For Windows use
	// exec("wget.exe -v -N --no-use-server-timestamps --append-output=wget_log.txt -O $dir $url");
	// For macOS/Linux use
	// exec("wget -v -N --no-use-server-timestamps --append-output=wget_log.txt -O $dir $url");
	exec("wget -v -N --no-use-server-timestamps --append-output=wget_log.txt -O $dir $url");

    continue;
}

echo "done \r\n\r\n";


?>
