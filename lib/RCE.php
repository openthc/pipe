<?php
/**
	An RCE Factory
*/

class RCE
{
	/**

	*/
	static function factory($cfg)
	{
		switch ($cfg['engine']) {
		case 'biotrack':

			$sid = $_SESSION['rce-auth']['session'];

			switch ($cfg['code']) {
			case 'hi':
				$rce = new RBE_BioTrack_HI($sid);
				break;
			case 'il':
				$rce = new RBE_BioTrack_IL($sid);
				break;
			case 'nd':
				$rce = new RBE_BioTrack_ND($sid);
				break;
			case 'nm':
				$rce = new RBE_BioTrack_NM($sid);
				break;
			case 'pr':
				$rce = new RBE_BioTrack_PR($sid);
				break;
			case 'wa/ucs':
				$rce = new RBE_BioTrack_WAUCS($sid);
				break;
			}

			break;

		case 'leafdata':

			$rce_auth = $_SESSION['rce-auth'];

			$l = new License();
			$l['id'] = null;
			$l['code'] = $rce_auth['license'];
			$rce_auth['license'] = $l;
			$rce_auth['secret'] = $rce_auth['client-key'];

			$rce = new RBE_LeafData($rce_auth);

			switch ($cfg['code']) {
			case 'leafdata-test':
			case 'wa-test':
				$rce->setTestMode();
				break;
			}

			break;

		case 'metrc':

			switch ($cfg['code']) {
			case 'ak':
				$rce = new RBE_Metrc_AK($_SESSION['rce-auth']);
				break;
			case 'ak-test':
				$rce = new RBE_Metrc_AK($_SESSION['rce-auth']);
				$rce->setTestMode();
				break;
			case 'ca':
				$rce = new RBE_Metrc_CA($_SESSION['rce-auth']);
				break;
			case 'ca-test':
				$rce = new RBE_Metrc_CA($_SESSION['rce-auth']);
				$rce->setTestMode();
				break;
			case 'co':
				$rce = new RBE_Metrc_CO($_SESSION['rce-auth']);
				break;
			case 'co-test':
				$rce = new RBE_Metrc_CO($_SESSION['rce-auth']);
				$rce->setTestMode();
				break;
			case 'nv':
				$rce = new RBE_Metrc_NV($_SESSION['rce-auth']);
				break;
			case 'nv-test':
				$rce = new RBE_Metrc_NV($_SESSION['rce-auth']);
				$rce->setTestMode();
				break;
			case 'or':
				$rce = new RBE_Metrc_OR($_SESSION['rce-auth']);
				break;
			case 'or-test':
				$rce = new RBE_Metrc_OR($_SESSION['rce-auth']);
				$rce->setTestMode();
				break;
			}

			break;

		}

		if (empty($rce)) {
			throw new \Exception(sprintf('Invalid RCE "%s" [ALR#099]', $cfg['code']));
		}

		return $rce;

	}

}
