# OpenTHC Pipe

The pipe is a common interface to multiple cannabis regulatory compliance engines.

Simply authenticate to the pipe system, which establishes a connection to the engine.
Then pull and push data.

Currently this system provides a READ-ONLY interface which is (or should be) compatible with all the systems.

## Authentication

### BioTrack

	curl \
		--data 'cre=usa/me' \
		--data 'username=user@example.com' \
		--data 'password=DoNotTe11!'


### LeafData

	curl \
		--data 'cre=usa/wa' \
		--data 'license=A123456' \
		--data 'license-key=SOMESEQUENCE'


### METRC

	curl \
		--data 'cre=usa/co' \
		--data 'program-key=ABC123' \
		--data 'license-key=ZYX987'


## Authentication

Authentication must take place with the system to interface with and that information is passed to the Pipe for authentication with the Compliance Reporting Engine ("CRE")


## Supported Compliance Engines

 * BioTrackTHC ("BT") - Delaware*, Hawaii, Maine, New Mexico, North Dakota, Illinois, Puerto Rico
 * Akerna/MJ Freeway/LeafData ("LD") - Washington, Utah*
 * METRC ("FM") - Alaska, California, Colorado, Massachusetts, Nevada, Oregon


## Supported Objects

 * Plants - (BT, LD, FM)
 * Inventory Lots (BT, LD, FM)
 * Laboratory Result (BT, LD, FM)
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


## Behat

	./vendor/bin/behat --snippets-for
