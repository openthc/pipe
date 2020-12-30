<?php
/**
 * A CRE Factory
 */

namespace App;

class CRE extends \OpenTHC\CRE\Base
{
	/**

	*/
	static function factory($cfg)
	{
		switch ($cfg['engine']) {
		case 'biotrack':

			$sid = $cfg['session_id'];

			switch ($cfg['code']) {
			case 'usa/hi':
				$cre = new \OpenTHC\CRE\BioTrack\Hawaii($sid);
				break;
			case 'usa/il':
				$cre = new \OpenTHC\CRE\BioTrack\Illinois($sid);
				break;
			case 'usa/nd':
				$cre = new \OpenTHC\CRE\BioTrack\NorthDakota($sid);
				break;
			case 'usa/nm':
				$cre = new \OpenTHC\CRE\BioTrack\NewMexico($sid);
				break;
			case 'usa/pr':
				$cre = new \OpenTHC\CRE\BioTrack\PuertoRico($sid);
				break;
			case 'usa/wa/ucs':
				$cre = new \OpenTHC\CRE\BioTrack\WAUCS($sid);
				break;
			}

			break;

		case 'leafdata':

			$cre_auth = $_SESSION['cre-auth'];
			// $l = array();
			// $l['id'] = null;
			// $l['code'] = $cre_auth['license'];
			// $cre_auth['license'] = $l;
			$cre_auth['secret'] = $cre_auth['license-key'];

			$cre = new \OpenTHC\CRE\LeafData($cre_auth);

			switch ($cfg['code']) {
			case 'usa/wa/test':
				$cre->setTestMode();
				break;
			}

			break;

		case 'metrc':

			switch ($cfg['code']) {
			case 'usa/ak':
			case 'usa/ak/test':
				$cre = new \OpenTHC\CRE\Metrc\Alaska($cfg);
				break;
			case 'usa/ca':
			case 'usa/ca/test':
				$cre = new \OpenTHC\CRE\Metrc\California($cfg);
				break;
			case 'usa/co':
			case 'usa/co/test':
				$cre = new \OpenTHC\CRE\Metrc\Colorado($cfg);
				break;
			case 'usa/ma':
			case 'usa/ma/test':
				$cre = new \OpenTHC\CRE\Metrc\Massachusetts($cfg);
				break;
			case 'usa/me':
			case 'usa/me/test':
				$cre = new \OpenTHC\CRE\Metrc\Maine($cfg);
				break;
			case 'usa/mi':
			case 'usa/mi/test':
				$cre = new \OpenTHC\CRE\Metrc\Michigan($cfg);
				break;
			case 'usa/mt':
			case 'usa/mt/test':
				$cre = new \OpenTHC\CRE\Metrc\Montana($cfg);
				break;
			case 'usa/nv':
			case 'usa/nv/test':
				$cre = new \OpenTHC\CRE\Metrc\Nevada($cfg);
				break;
			case 'usa/or':
			case 'usa/or/test':
				$cre = new \OpenTHC\CRE\Metrc\Oregon($cfg);
				break;
			}

			if ('test' == basename($cfg['code'])) {
				$cre->setTestMode();
			}

			break;

		}

		if (empty($cre)) {
			throw new \Exception(sprintf('Invalid CRE "%s" [ALR-099]', $cfg['code']));
		}

		return $cre;

	}
}
