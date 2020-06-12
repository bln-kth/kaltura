# kaltura-backup-media-by-user
Run this from a **empty** directory, all download files will be writen here.\
Creates also file backup-media-by-user.csv with "media id, Created at, Entry name, Download URL, Filename"

### api url
* Public site = `www.kaltura.com`
* Nordunet site = `api.kaltura.nordu.net`

## To run
* Install/Download python:
https://www.python.org/downloads/
* Install/Download pip
https://pip.pypa.io/en/stable/installing/
* Install deps:
`pip install KalturaApiClient`
* Run the script:
`python backup-media-by-user.py username@kaltura.com`
