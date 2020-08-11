import sys,hashlib
from KalturaClient import *
from KalturaClient.Plugins.Core import *

import apptoken

config = KalturaConfiguration(apptoken.partner_id)
config.serviceUrl = "https://api.kaltura.nordu.net/"
client = KalturaClient(config)

# GENERATE SESSION

widgetId = "_"+str(apptoken.partner_id)
expiry = 86400
result = client.session.startWidgetSession(widgetId, expiry)
client.setKs(result.ks)
print (result.ks)

ATHash = hashlib.sha256(result.ks.encode('ascii')+apptoken.tokenHash.encode('ascii')).hexdigest()
type = KalturaSessionType.ADMIN 

result = client.appToken.startSession(apptoken.tokenid, ATHash, apptoken.user_id, type, expiry)
client.setKs(result.ks)
print (result.ks)