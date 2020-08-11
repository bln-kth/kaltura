import sys,hashlib
from KalturaClient import *
from KalturaClient.Plugins.Core import *

import apptoken

print(apptoken.partner_id)
print(apptoken.tokenid)
print(apptoken.tokenHash)
print(apptoken.adminuser_id)

partner_id="your partner ID"
config = KalturaConfiguration(apptoken.partner_id)
config.serviceUrl = "https://api.kaltura.nordu.net/"
client = KalturaClient(config)

id="apptokenid"
token="apptoken"
userId=""

# GENERATE SESSION

widgetId = "_"+str(apptoken.partner_id)
expiry = 86400
result = client.session.startWidgetSession(widgetId, expiry)
client.setKs(result.ks)
tokenHash = hashlib.sha256(result.ks.encode('ascii')+token.encode('ascii')).hexdigest()
type = KalturaSessionType.ADMIN 

result = client.appToken.startSession(apptoken.tokenid, apptoken.tokenHash, apptoken.adminuser_id, type, expiry)
client.setKs(result.ks)