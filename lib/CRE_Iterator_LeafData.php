<?php
/**
	A Better Iterator for LeafData
*/

class CRE_Iterator_LeafData extends CRE_Iterator
{

	function __construct($api, $arg=null)
	{
		parent::__construct($api, $arg);

		$this->_data_buf = new \ArrayIterator(array());
		$this->_loadData();

	}

	function _loadData()
	{
		// echo "_loadData({$this->_data_idx})<br>";

		// Don't load if loaded
		//if (count($this->_data_buf)) {
		//	if ($this->_data_idx < $this->_data_max) {
		//		return;
		//	}
		//}

		if ($this->_page_cur <= $this->_page_max) {

			$arg = $this->_arg;
			$arg['page'] = $this->_page_cur;

			$res = $this->_api->all($arg);

			// Retry?
			if ('success' != $res['status']) {
				$this->_data_buf = new \ArrayIterator(array());
				return(null);
			}

			$this->_page_cur++;

			if (!empty($res['result']['last_page'])) {
				$this->_page_max = $res['result']['last_page'];
			}

			if (!empty($res['result']['data'])) {
				$res = $res['result']['data'];
			} elseif (!empty($res['result']) && !empty($res['result'][0]['global_id'])) {
				$res = $res['result'];
			} else {
				$res = array();
			}

			// On Page 1 for LeafData it's OK
			// On Page 2 this one has an index-key assigned (eg: '2500')
			//$this->_data_buf = array_values($res['result']['data']);
			$this->_data_buf = new \ArrayIterator(array_values($res));
			//$this->_data_idx = 0;
			//$this->_data_max = count($this->_data_buf);
		}

	}

	function current() { /* echo "current({$this->_data_idx})<br>"; */ return $this->_data_buf->current(); }
	function next() { /* echo "next()<br>"; */ $this->_data_idx++; return $this->_data_buf->next(); }
	function valid()
	{
		//echo "valid()<br>";
		$v = $this->_data_buf->valid();
		if (!$v) {
			$this->_loadData();
		}

		return $this->_data_buf->valid();

	}
	function rewind() { return $this->_data_buf->rewind(); }

}
