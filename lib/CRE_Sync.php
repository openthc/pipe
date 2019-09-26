<?php
/**
 * CRE Sync Tools for the Pipe+Stem
 */

use Edoceo\Radix\DB\SQL;

class CRE_Sync
{
	const MAX_AGE = 240; // Seconds

	/**
		Returns Age in Seconds
		@param $name Name to Find
		@param $time if null, return age; if set commit that value
	*/
	static function age($name, $time=null)
	{
		// Setting Value
		if (!empty($time)) {
			$arg = array("sync-{$name}-time", time());
			SQL::query("INSERT OR REPLACE INTO _config (key, val) VALUES (?, ?)", $arg);
			return true;
		}

		// Reading Age, not
		$dt0 = $_SERVER['REQUEST_TIME'];
		$sql = sprintf("SELECT val FROM _config WHERE key = 'sync-{$name}-time'");
		$dt1 = intval(SQL::fetch_one($sql));
		$age = $dt0 - $dt1;

		if ('false' == $_GET['cache']) {
			$age = 301;
		}

		return $age;
	}

	/**
		@param $name Object Name
		@param $guid Object GUID
		@param $hash Object Hash
		@param $data Object Data Array
	*/
	static function save($name, $guid, $hash, $data)
	{
		$sql = "INSERT OR REPLACE INTO {$name} (guid, hash, meta) VALUES (:guid, :hash, :meta)";
		$arg = array(
			':guid' => $guid,
			':hash' => $hash,
			':meta' => json_encode($data),
		);

		SQL::query($sql, $arg);
	}

}
