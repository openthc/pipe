# OpenTHC Pipe

The pipe is a common interface to multiple cannabis regulatory compliance engines.

Simply authenticate to the pipe system, which establishes a connection to the engine.
Then pull and push data using the OpenTHC API specification.

Currently this system provides a READ-ONLY interface which is (or should be) compatible with all the systems.

## Installation

* Clone this Repo
* Run `composer update`
* Copy ./etc/cre.ini.example to ./etc/cre.ini and configure it.
* Change owner and permissions on ./var so the web-server can read/write

## Services

Pipe provides two services: the Pipe service itself and the "Stem" service.
Pipe provides some intelligence and ETLs on the CRE data, re-mapping into the OpenTHC API specifications.
Stem is a caching and logging service for communications with the CRE.

It's possible to use Stem alone with your own code for communications with the CRE service.
You'll get those caching and logging benefits.

Using Pipe will rewrite the CRE data into the OpenTHC API formats.


## Authentication

Authentication must take place with the backend compliance reporting engine ("CRE").
The authentication for pipe itself is just a passthru to the selected backend system.
Pipe then maintains a session state via headers, cookie or query-string parameters.
The authentication parameters are determined by the `cre` selection.


### BioTrack

These systems authenticate with a company, username and password

	curl \
		--data 'cre=usa/nm' \
		--data 'company=123456789' \
		--data 'username=user@example.com' \
		--data 'password=DoNotTe11!'


### LeafData

These systems use a License ID and a Contact Key

	curl \
		--data 'cre=usa/wa' \
		--data 'license=A123456' \
		--data 'license-key=SOMESEQUENCE'


### METRC

These system require a program key and contact key.
The program key is given to the software provider by METRC and is configured in cre.ini.
The contact key is from the licensee, its permissions will be determined by the user which acquires it.
For best results, use a key from an administrator / super-user type role.

	curl \
		--data 'cre=usa/co' \
		--data 'program-key=ABC123' \
		--data 'license-key=ZYX987'


## Authentication


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
