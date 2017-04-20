<?php
class HTTP{
	private $url;
	private $header;
	private $postStr;
	private $postArray;
	private $headerResp;
	//indicate if follow redirects
	private $isFollowLocation;
	//indicate if wants header back in response
	private $isHeader;
	private $headerCode;
	private $proxy;
	//default parameters:
	//do NOT follow redirects
	//do NOT transfer header
	//postStr is Null string
	function __construct($u="")
	{
		$this->header = array();
		$this->postArray = array();
		$this->isFollowLocation = false;
		$this->isHeader = false;
		$this->url = $u;
		$this->postStr = "";
		$this->headerResp = "";
		$this->headerCode = 0;
		$this->proxy = "";
	}
	function __destruct()
	{
		$this->close();
	}
	function setProxy($p)
	{
		$this->proxy = $p;
	}
	function setURL($u)
	{
		$this->url = $u;
	}
	public function getRespCode()
	{
		return $this->headerCode;
	}
	public function getRespHeader()
	{
		return $this->headerResp;
	}
	public function setReferer($r)
	{
		$this->setHeader("Referer", $r);
	}
	public function setCookie($c)
	{
		$this->setHeader("Cookie", $c);
	}
	public function setUserAgent($u)
	{
		$this->setHeader("User-Agent", $u);
	}
	public function setTransferHeader($h)
	{
		$this->isHeader = $h;
	}
	public function setFollowLocation($fl)
	{
		$this->isFollowLocation = $fl;
	}
	//assemble the header based on $header
	//return value is the headerArray used in curl
	private function constructHeader()
	{
		$headerArray = array();
		$headerCount = 0;
		foreach($this->header as $key => $val)
			$headerArray[$headerCount++] = $key.":".$val;
		return $headerArray;
	}
	//set the header item
	//e.g. setHeader("cookie", "a=b")
	public function setHeader($name, $value)
	{
		$this->header[$name] = $value;
	}
	//remove the header item
	public function removeHeader($name)
	{
		unset($this->header[$name]);
	}
	//set the post item 
	public function setPostItem($name, $value)
	{
		$this->postArray[$name] = $value;
	}
	//remove the post item
	public function removePostItem($name)
	{
		unset($this->postArray[$name]);
	}
	//set post string
	public function setPostStr($str)
	{
		$this->postStr = $str;
	}
	public function get($u=null)
	{
		if(!empty($u))
			$this->url = $u;
		$ch = $this->initCurl();
		$result = curl_exec($ch);
		//separate header and body
		if($this->isHeader)
		{
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$this->headerResp = substr($result, 0, $header_size);
			$body = substr($result, $header_size);	
			$this->headerCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);			
			return $body;
		}
		else
		{
			$this->headerCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);			
			return $result;
		}
	}
	//construct post str
	private function constructPostStr()
	{
		foreach($this->postArray as $key => $val)
		{
			if($this->postStr != "")
				$this->postStr = $this->postStr."&$key=$val";
			else
				$this->postStr = "$key=$val";
		}
	}
	public function post($u=null)
	{
		if(!empty($u))
			$this->url = $u;
		$this->constructPostStr();
		$ch = $this->initCurl();
		curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postStr);
		$result = curl_exec($ch);
		//separate header and body
		if($this->isHeader)
		{
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$this->headerResp = substr($result, 0, $header_size);
			$body = substr($result, $header_size);	
			$this->headerCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return $body;
		}
		else
		{
			$this->headerCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return $result;
		}			
	}
	private function initCurl()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_HEADER, $this->isHeader);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->isFollowLocation);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		if($this->proxy != "")
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		//test if user-agent is set
		if(!isset($this->header["User-Agent"]))
			$this->setUserAgent("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36");
		$harray = $this->constructHeader();
		curl_setopt($ch, CURLOPT_HTTPHEADER, $harray);
		return $ch;
    } 
    public function close()
	{
	}
}
?>