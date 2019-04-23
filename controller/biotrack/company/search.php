<?php
/**
 * BioTrack doesn't do Companies only License
 * @todo we could filter on the License table for common UBIs
 */

return $RES->withJSON(array(
	'status' => 'failure',
	'detail' => 'Not Implemented in BioTrack Systems - use /config/license and filter',
), 501, JSON_PRETTY_PRINT);
