import sys, csv, datetime, requests, re
from requests.exceptions import RequestException

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

with open('backup-media-by-user.csv', mode='w') as f_file:
	f_writer = csv.writer(f_file, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
	f_writer.writerow(["entry_id", "createdAt", "entry_name", "downloadUrl", "filename"])

# Loop
with open('backup-media-by-user.csv', mode='a') as f_file:
	f_writer = csv.writer(f_file, delimiter=',', quotechar='"', quoting=csv.QUOTE_MINIMAL)
	nid = 1
	while nid < totalcount :
		entrylist = client.media.list(filter, pager)
		for entry in entrylist.objects:
			createdat = datetime.datetime.fromtimestamp(int(entry.createdAt)).strftime('%Y-%m-%d %H:%M:%S')
			
			try:
				with requests.get(entry.downloadUrl) as r:

					fname = ''
					if "Content-Disposition" in r.headers.keys():
						fname = re.findall("filename=(.+)", r.headers["Content-Disposition"])[0]
					else:
						fname = entry.downloadUrl.split("/")[-1]

					#print("nr2",fname)
			except RequestException as e:
				print(e)

			fname = fname.replace('"', '')
			fname = fname.replace('/', '-')
			fname = fname.replace(':', '-')

			ok_typ = [".mp4",".mov",".avi",".jpg",".noex"]
			
			if not fname.endswith(tuple(ok_typ)):
				fname = fname + ".mp4"
			
			fname = entry.id+"-"+fname
			
			f_writer.writerow([entry.id, createdat, entry.name, entry.downloadUrl, fname])

			print ("Downloding",fname)

			open(fname, 'wb').write(r.content)
			r = requests.get(entry.downloadUrl)
			
			nid = nid + 1
		pager.pageIndex = pager.pageIndex + 1
