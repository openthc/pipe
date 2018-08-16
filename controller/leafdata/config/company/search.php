<?php
/**
	LeafData Company List
	They don't really support this (and neither do we)
	@todo Maybe look at pulling all licenses and grouping by UBI (in WA) or Name?
*/

return $this->withJSON(array(
	'status' => 'failure',
	'detail' => 'Company Interface not supported for LeafData [CCS#012]',
), 404);
