import sys, csv
from pprint import pprint
from KalturaClient import *
from KalturaClient.Plugins.Core import *
sys.path.append('../')
import private

try:
	csv_file = sys.argv[1]
except:
	csv_file = input("csv file: ")
else:
	csv_file = sys.argv[1]

config = KalturaConfiguration()
config.serviceUrl = "https://api.kaltura.nordu.net/"
client = KalturaClient(config)
ks = client.session.start(
	private.secret,
	private.adminuser_id,
	KalturaSessionType.ADMIN,
	private.partner_id)
client.setKs(ks)

f = open(csv_file)
csv_f = csv.reader(f)

for row in csv_f:
	if not str(row[0]).startswith('#'):
		name = row[0]
		userid = row[1]
		tags = row[2]
		description = row[3]
		filename = row[4]
		
		uploadToken = KalturaUploadToken()

		result = client.uploadToken.add(uploadToken)
		#pprint(vars(result))
		uploadTokenId = result.id
		fileData = open(filename, 'rb')
		resume = False
		finalChunk = True
		resumeAt = -1

		result = client.uploadToken.upload(uploadTokenId, fileData, resume, finalChunk, resumeAt)
		#pprint(vars(result))
		entry = KalturaMediaEntry()
		entry.mediaType = KalturaMediaType.VIDEO
		entry.name = name
		entry.userId = userid
		entry.tags = tags
		entry.description = description

		result = client.media.add(entry)
		#pprint(vars(result))
		entryId = result.id
		#print(entryId)
		resource = KalturaUploadedFileTokenResource()
		resource.token = uploadTokenId

		result = client.media.addContent(entryId, resource)
		#pprint(vars(result))
