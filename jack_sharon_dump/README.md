# kaltura-jack_sharon_dump
Download entry from Kaltura

### api url
* Public site = `www.kaltura.com`
* Nordunet site = `api.kaltura.nordu.net`

## To run
* Install/Download php:
https://www.php.net/downloads.php
* Install/Download wget
https://www.gnu.org/software/wget/

Now in detail what is in there. First the key scripts that are needed to generate three export CSV files that can be opened by Excel:\
`php dump_audio.php`\
`php dump_video.php`\
`php dump_image.php`
 
Then there are two scripts to automatically pull down batches of captions and media files from Kaltura:
`php bulk_download_captions.php`\
`php bulk_download_media.php`
 
A config file to enter the necessary config values for your instance
 
config.ini
 
A tiny test file to see if PHP is working before you start
 
hello_world.php
 
Two text files used to feed the download scripts with the relevant media or captions id values retrieved from the CSV files
 
ids_for_captions_download.txt
ids_for_media_download.txt


* Run one of the scripts:\
`php dump_audio.php`\
`php dump_video.php`\
`php dump_image.php`\
`php bulk_download_captions.php`\
`php bulk_download_media.php`
