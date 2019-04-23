# OpenTHC Pipe

The pipe is a common interface to multiple cannabis regulatory compliance engines.

Simply authenticate to the pipe system, which establishes a connection to the engine.
Then pull and push data.

Currently this system provides a READ-ONLY interface which is (or should be) compatible with all the systems.

## Authentication

### BioTrack

	curl \
		--data 'rce=usa/hi' \
		--data 'username=user@example.com' \
		--data 'password=DoNotTe11!'


### LeafData

	curl \
		--data 'rce=usa/wa' \
		--data 'license=A123456' \
		--data 'license-key=SOMESEQUENCE'


### METRC

	curl \
		--data 'rce=usa/co' \
		--data 'program-key=ABC123' \
		--data 'license-key=ZYX987'


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

	curl /plant
	curl /plant?filter=(active|wet-collect|dry-collect|done|dead)
	curl /plant?filter=and&f-[n0]=[v0]&f-[n1]=[v1]
	curl /plant?filter=or&f-[n0]=[v0]&f-[n1]=[v1]


## Reading Inventory

	curl /lot
	curl /lot?filter=(active)
	curl /lot?filter=and
	curl /lot?filter=or


## Reading Transfer Data

	curl /transfer
	curl /transfer/outgoing?filter=(active)
	curl /transfer/incoming?filter=(active)
	curl /transfer?filter=and
	curl /transfer?filter=or


### Supported Systems

 * BioTrackTHC - Hawaii, Illinois, North Dakota, New Mexico, Washington (UCS)
 * LeafData - Washington
 * METRC - Alaska, California, Colorado, Nevada, Oregon

## Behat

	./vendor/bin/behat --snippets-for
