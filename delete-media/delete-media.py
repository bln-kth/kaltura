from KalturaClient import *
from KalturaClient.Plugins.Core import *
import sys, datetime
import private

try:
	entryId = sys.argv[1]
except:
	entryId = input("entryId to delete: ")
else:
	entryId = sys.argv[1]

def yes_or_no():
    YesNo = input("Yes or No?")
    YesNo = YesNo.lower()
    if(YesNo == "yes"):
        return 1
    elif(YesNo == "no"):
        return 0
    else:
        return yes_or_no()

config = KalturaConfiguration()
config.serviceUrl = "https://api.kaltura.nordu.net/"
client = KalturaClient(config)
ks = client.session.start(
	private.secret,
	private.adminuser_id,
	KalturaSessionType.ADMIN,
	private.partner_id)
client.setKs(ks)

entry = client.media.get(entryId, 0)

createdat = datetime.datetime.fromtimestamp(int(entry.createdAt)).strftime('%Y-%m-%d %H:%M:%S')
text = str(createdat)+"\t"+entry.id+"\t"+entry.name+"\t"+entry.userId+"\t"+entry.tags
print(text)
print ("Delte this entry?")

answer = yes_or_no()
if answer == 1:
    print("Deleting",entryId,"...")
else:
    exit()

result = client.media.delete(entryId)
