# kaltura-jack_sharon_dump
Download entry from Kaltura, you need to run and edit som scripts. See below.

### api url
* Public site = `www.kaltura.com`
* Nordunet site = `api.kaltura.nordu.net`

### To Install/Download
* Install/Download php:
https://www.php.net/downloads.php
* Install/Download wget
https://www.gnu.org/software/wget/

### To test php
`php hello_world.php`

## Some info
Now in detail what is in there. First the key scripts that are needed to generate three export CSV files that can be opened by Excel:\
`php dump_audio.php`\
`php dump_video.php`\
`php dump_image.php`
 
Then there are two scripts to automatically pull down batches of captions and media files from Kaltura:\
This scripts needs to change if you run Windows, macOS or Linux\
`php bulk_download_captions.php`\
`php bulk_download_media.php`
 
A config file to enter the necessary config values for your instance:\
`config.ini`

Two text files used to feed the download scripts with the relevant media or captions id values retrieved from the CSV files.
`ids_for_captions_download.txt`
`ids_for_media_download.txt`

A blank /output folder  and the Kaltura PHP client API folder in /php5 – just leave it sit there.
