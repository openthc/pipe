<?php
/**
	An RCE Factory
*/

class RCE
{
	/**

	*/
	static function factory($rbe)
	{
		switch ($rbe['engine']) {
		case 'biotrack':

			switch ($rbe['agency']) {
			case 'hi':
				$rce = new RBE_BioTrack_HI($_SESSION['rbe-auth']);
				break;
			case 'il':
				$rce = new RBE_BioTrack_IL($_SESSION['rbe-auth']);
				break;
			case 'nd':
				$rce = new RBE_BioTrack_ND($_SESSION['rbe-auth']);
				break;
			case 'nm':
				$rce = new RBE_BioTrack_NM($_SESSION['rbe-auth']);
				break;
			case 'pr':
				$rce = new RBE_BioTrack_PR($_SESSION['rbe-auth']);
				break;
			case 'wa/ucs':
				$rce = new RBE_BioTrack_WAUCS($_SESSION['rbe-auth']);
				break;
			}

			break;

		case 'leafdata':

			$rce = new RBE_LeafData($_SESSION['rbe-auth']);

			switch ($rbe['code']) {
			case 'leafdata-test':
			case 'wa-test':
				$rce->setTestMode('work');
				break;
			}

			break;

		case 'metrc':

			switch ($rbe['code']) {
			case 'ak':
				$rce = new RBE_Metrc_AK($_SESSION['rbe-auth']);
				break;
			case 'ak-test':
				$rce = new RBE_Metrc_AK($_SESSION['rbe-auth']);
				$rce->setTestMode();
				break;
			case 'ca':
				$rce = new RBE_Metrc_CA($_SESSION['rbe-auth']);
				break;
			case 'ca-test':
				$rce = new RBE_Metrc_CA($_SESSION['rbe-auth']);
				$rce->setTestMode();
				break;
			case 'co':
				$rce = new RBE_Metrc_CO($_SESSION['rbe-auth']);
				break;
			case 'co-test':
				$rce = new RBE_Metrc_CO($_SESSION['rbe-auth']);
				$rce->setTestMode();
				break;
			case 'nv':
				$rce = new RBE_Metrc_Nevada($_SESSION['rbe-auth']);
				break;
			case 'nv-test':
				$rce = new RBE_Metrc_Nevada($_SESSION['rbe-auth']);
				$rce->setTestMode();
				break;
			case 'or':
				$rce = new RBE_Metrc_Oregon($_SESSION['rbe-auth']);
				break;
			case 'or-test':
				$rce = new RBE_Metrc_Oregon($_SESSION['rbe-auth']);
				$rce->setTestMode();
				break;
			}

			break;

		}

		if (empty($rce)) {
			throw new \Exception('Invalid RCE');
		}

		return $rce;

	}

}
