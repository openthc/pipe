# OpenTHC Pipe

The pipe is a common interface to multiple cannabis compliance engines.

Simply authenticate to the pipe system, which establishes a connection to the engine.
Then pull and push data.

Currently this system provides a READ-ONLY interface which is (or should be) compatible with all the systems.

## Authentication

### BioTrack

	curl \
		--data 'rce=hi' \
		--data 'username=user@example.com' \
		--data 'password=DoNotTe11!' \

### LeafData

	curl \
		--data 'rce=wa' \
		--data 'license=A123456' \
		--data 'api-key=SOMESEQUENCE' \

	f_batch_id=WAL876986.BA2E2SN
	f_updated_at1=11/1/2017&
	f_updated_at2=11/30/2017

### METRC

	curl \
		--data 'rce=co' \
		--data 'vendor-key=ABC123' \
		--data 'client-key=ZYX987' \


## Authentication

Authentication must take place with the system to interface with and that information is passed to the OACI for authentication with the Regulatory Compliance Engine ("RCE")

## Supported Compliance Engines

 * BioTrackTHC ("BT") - Delaware*, Hawaii, New Mexico, North Dakota, Illinois, Puerto Rico
 * LeafData ("LD") - Washington
 * METRC ("FM") - Alaska, California, Colorado, Nevada, Oregon

## Supported Objects

 * Plants - (BT, LD, FM)
 * Inventory Lots (BT, LD, FM)
 * Products (LD, FM)
 * Zones (aka: Areas, Rooms) - (BT, LD, FM)
 * Vehicles - BT

## Reading Plants

	curl /plants
	curl /plants?filter=(active|wet-collect|dry-collect|done|dead)
	curl /plants?filter=and&f-[n0]=[v0]&f-[n1]=[v1]
	curl /plants?filter=or&f-[n0]=[v0]&f-[n1]=[v1]

## Reading Inventory

	curl .../inventory
	curl .../inventory?filter=(active)
	curl .../inventory?filter=and
	curl .../inventory?filter=or


### Supported Systems

 * BioTrackTHC/HI
 * BioTrackTHC/IL
 * BioTrackTHC/ND
 * BioTrackTHC/NM
 * LeafData/WA
 * METRC/AK
 * METRC/CA
 * METRC/CO
 * METRC/NV
 * METRC/OR


## Behat

	./vendor/bin/behat --snippets-for
