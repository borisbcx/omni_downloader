<?php
class Extractor{
	protected $vid;
	protected $url;
	protected $http;
	function __construct($u="")
	{
		$this->vid = "";
		$this->url = $u;
		$this->http = new HTTP();
	}
	function __destruct()
	{
		
	}
	public function setURL($u)
	{
		$this->url = $u;
	}
	public function setVid($v)
	{
		$this->vid = $v;
	}
	public function getVid()
	{
		if($this->vid == "")
			$this->getVidFromURL();
		return $this->vid;
	}
}
?>