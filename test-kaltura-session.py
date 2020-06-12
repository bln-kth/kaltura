from KalturaClient import *
from KalturaClient.Plugins.Core import *

sys.path.append('../')
import private

config = KalturaConfiguration()
config.serviceUrl = "https://api.kaltura.nordu.net/"
client = KalturaClient(config)
ks = client.session.start(
	private.secret,
	private.adminuser_id,
	KalturaSessionType.ADMIN,
	private.partner_id)
print (client.setKs(ks))
