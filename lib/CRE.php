<?php
/**
 * An CRE Factory
 */

class CRE
{
	/**

	*/
	static function factory($cfg)
	{
		switch ($cfg['engine']) {
		case 'biotrack':

			$sid = $_SESSION['cre-auth']['session'];

			switch ($cfg['code']) {
			case 'usa/hi':
				$cre = new RBE_BioTrack_HI($sid);
				break;
			case 'usa/il':
				$cre = new RBE_BioTrack_IL($sid);
				break;
			case 'usa/nd':
				$cre = new RBE_BioTrack_ND($sid);
				break;
			case 'usa/nm':
				$cre = new RBE_BioTrack_NM($sid);
				break;
			case 'usa/pr':
				$cre = new RBE_BioTrack_PR($sid);
				break;
			case 'usa/wa/ucs':
				$cre = new RBE_BioTrack_WAUCS($sid);
				break;
			}

			break;

		case 'leafdata':

			$cre_auth = $_SESSION['cre-auth'];
			// $l = array();
			// $l['id'] = null;
			// $l['code'] = $cre_auth['license'];
			// $cre_auth['license'] = $l;
			$cre_auth['secret'] = $cre_auth['client-key'];

			$cre = new RBE_LeafData($cre_auth);

			switch ($cfg['code']) {
			case 'usa/wa/test':
				$cre->setTestMode();
				break;
			}

			break;

		case 'metrc':

			switch ($cfg['code']) {
			case 'ak':
				$cre = new RBE_Metrc_AK($_SESSION['cre-auth']);
				break;
			case 'ak-test':
				$cre = new RBE_Metrc_AK($_SESSION['cre-auth']);
				$cre->setTestMode();
				break;
			case 'ca':
				$cre = new RBE_Metrc_CA($_SESSION['cre-auth']);
				break;
			case 'ca-test':
				$cre = new RBE_Metrc_CA($_SESSION['cre-auth']);
				$cre->setTestMode();
				break;
			case 'co':
				$cre = new RBE_Metrc_CO($_SESSION['cre-auth']);
				break;
			case 'co-test':
				$cre = new RBE_Metrc_CO($_SESSION['cre-auth']);
				$cre->setTestMode();
				break;
			case 'nv':
				$cre = new RBE_Metrc_NV($_SESSION['cre-auth']);
				break;
			case 'nv-test':
				$cre = new RBE_Metrc_NV($_SESSION['cre-auth']);
				$cre->setTestMode();
				break;
			case 'or':
				$cre = new RBE_Metrc_OR($_SESSION['cre-auth']);
				break;
			case 'or-test':
				$cre = new RBE_Metrc_OR($_SESSION['cre-auth']);
				$cre->setTestMode();
				break;
			}

			break;

		}

		if (empty($cre)) {
			throw new \Exception(sprintf('Invalid CRE "%s" [ALR#099]', $cfg['code']));
		}

		return $cre;

	}

}
