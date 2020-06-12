import sys, datetime

try:
	user = sys.argv[1]
except:
	user = input("Kaltura user: ")
else:
	user = sys.argv[1]
    
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
client.setKs(ks)

# Get list
filter = KalturaMediaEntryFilter()
filter.orderBy = "-createdAt" # Newest first
#filter.orderBy = "+createdAt" # Oldest first
filter.userIdEqual = user
pager = KalturaFilterPager()
#pager.pageSize = 500
pager.pageIndex = 1

entrylist = client.media.list(filter, pager)
totalcount = entrylist.totalCount

with open('list-media-by-user.txt', 'w') as f:
    text ="entry.id\tcreatedAt\tentry.name\tentry.downloadUrl"
    #print(text)
    f.write(text+"\n")

# Loop
with open('list-media-by-user.txt', 'a') as f:
	nid = 1
	while nid < totalcount :
		entrylist = client.media.list(filter, pager)
		for entry in entrylist.objects:
			createdat = datetime.datetime.fromtimestamp(int(entry.createdAt)).strftime('%Y-%m-%d %H:%M:%S')
			text = entry.id+"\t"+createdat+"\t"+entry.name+"\t"+entry.downloadUrl
			#print (text)
			f.write(text+"\n")
			nid = nid + 1
		pager.pageIndex = pager.pageIndex + 1
