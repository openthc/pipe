<?php
/**
	An Interator for CRE Stuff
*/

/**
	Lifecycle:
	Construct => Rewind => Valid() => Current()
		Next => Valid => Current
*/
class CRE_Iterator implements Iterator
{
	protected $_api;
	protected $_arg;

	protected $_page_cur = 1;
	protected $_page_max = 1;

	protected $_data_buf = array();
	protected $_data_idx = 0;
	protected $_data_max = 0;
	protected $_is_valid = true;

	function __construct($api, $arg=null)
	{
		$this->_api = $api;

		$this->_arg = $arg;
		if (empty($this->_arg)) {
			$this->_arg = array();
		}
	}

	function current()
	{
		//echo "current({$this->_data_idx})<br>";
		$this->_loadData();
		return $this->_data_buf[$this->_data_idx];
	}

	function key()
	{
		//echo "key()<br>";
	}

	function next()
	{
		$this->_data_idx++;
		if ($this->_data_idx > $this->_data_max) {
			$this->_is_valid = false;
		}
	}

	function rewind()
	{
		$this->_page_cur = 1;
		$this->_page_max = 1;
		$this->_data_buf = array();
		$this->_data_idx = 0;
		$this->_is_valid = true;
	}

	function valid()
	{
		return $this->_is_valid;
	}
}
