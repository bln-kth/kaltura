# kaltura
This is api scripts for Kaltura, using Python 3.6 (or later) with pip3

### api url
* Public site = `www.kaltura.com`
* Nordunet site = `api.kaltura.nordu.net`

## Fix Kaltura api Integration
At KMC Integration Settings on Kaltura, you can find Administrator Secret & Partner ID\
Edit private.py with Integration Settings and Administrator e-mail

## Test Kaltura api Integration
* Install/Download python:
https://www.python.org/downloads/
* Install/Download pip
https://pip.pypa.io/en/stable/installing/
* Install deps:
`pip install KalturaApiClient`
* Run the script:
`python test-kaltura-session.py`

## Optional Create App Token
* Create a App Token, see [apptoken](apptoken-mgmt/)
* Edit app-token.py with App Tokens, Partner ID and User e-mail
* Test App Token Session:
`python test-app-token.py`

### Info
>This is Open Source and it´s free to use or change.\
Use all this scripts at **your own risk**. There is **no** support or help included.
