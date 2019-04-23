<?php
/**
 * Transfers / Rejected
 */

return $RES->withJSON(array(
	'status' => 'failure',
	'detail' => 'Not Implemented in BioTrack Systems - use /transfer/outgoing and filter',
), 501, JSON_PRETTY_PRINT);
