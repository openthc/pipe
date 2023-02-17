# OpenTHC PIPE

The PIPE is a logging proxy for multiple cannabis regulatory compliance engines.
It's basically an intentional MITM for these upstream proxy.

Built before we learned about [krakend.io](https://www.krakend.io/).
Also checkout [openapi-cop](https://github.com/EXXETA/openapi-cop).


## Installation

* Clone this Repo
* Run `composer update`
* Copy ./etc/cre.ini.example to ./etc/cre.ini and configure it.
* Change owner and permissions on ./var so the web-server can read/write

## Authentication

Authentication must take place with the backend compliance reporting engine ("CRE").
The authentication for pipe itself is just a passthru to the selected backend system.
PIPE then maintains a session state via headers, cookie or query-string parameters.
The authentication parameters are determined by the `cre` selection.

## Supported Compliance Engines

 * BioTrackTHC ("BT") - Delaware*, Hawaii, Maine, New Mexico, North Dakota, Illinois, Puerto Rico
 * Akerna/MJ Freeway/LeafData ("LD") - Pennsylvania*, Washington*, Utah*
 * Metrc ("FM") - Alaska, California, Colorado, Massachusetts, Nevada, Oregon


### BioTrack

These systems authenticate with a company, username and password

	curl \
		--data 'company=123456789' \
		--data 'username=user@example.com' \
		--data 'password=DoNotTe11!'


### LeafData

These systems use a License ID and a Contact Key.
This system is in use, but closed in Pennsylvania; no longer used in Washington and we don't know the status of Utah

	curl \
		--data 'license=A123456' \
		--data 'license-key=SOMESEQUENCE'


### Metrc

These system require a program key and contact key.
The program key is given to the software provider by Metrc and is configured in cre.ini.
The contact key is from the licensee, its permissions will be determined by the user which acquires it.
For best results, use a key from an administrator / super-user type role.

	curl \
		--data 'program-key=ABC123' \
		--data 'license-key=ZYX987'


## Supported Objects

 * Plants - (BT, LD, FM)
 * Inventory Lots (BT, LD, FM)
 * Laboratory Result (BT, LD, FM)
 * Products (LD, FM)
 * Zones (aka: Areas, Rooms) - (BT, LD, FM)
 * Vehicles - BT



### See Also

* https://news.ycombinator.com/item?id=22482031
