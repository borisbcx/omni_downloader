<?php
include_once(__DIR__.'/extractor.php');
include_once(__DIR__.'/../Utility/http.php');
include_once(__DIR__.'/../Result/youku_video_result.php');
class YoukuExtractor extends Extractor{
	public static $stream_types = array(
        'mp4hd3' => array('alias-of' => 'hd3'),
        'hd3' => array('container'=> 'flv', 'video_profile'=> '1080P'),
        'mp4hd2' => array('alias-of' => 'hd2'),
        'hd2' => array('container'=> 'flv', 'video_profile'=> '超清'),
        'mp4hd'=> array('alias-of' => 'mp4'),
        'mp4'=> array('container'=> 'mp4', 'video_profile'=> '高清'),
        'flvhd'=> array('container'=> 'flv', 'video_profile'=> '标清'),
        'flv'=> array('container'=> 'flv', 'video_profile'=> '标清'),
        '3gphd'=> array('container'=> '3gp', 'video_profile'=> '标清（3GP）')
    );
	public static $f_code_1 = 'becaf9be';
	public static $f_code_2 = 'bf7e5f01';
	function __construct($u="")
	{
		parent::__construct($u);
		$this->http->setReferer("http://static.youku.com/");
	}
	function __destruct()
	{
		
	}
	private function getVidFromURL()
	{
		preg_match('/youku.com\/v_show\/id_([0-9a-zA-Z=]+)/', $this->url, $match);
		if(count($match) >= 2)
			$this->vid = $match[1];
		preg_match('/player.youku.com\/player.php\/sid\/([0-9a-zA-Z=]+)\/v.swf/', $this->url, $match);
		if(count($match) >= 2)
			$this->vid = $match[1];
		preg_match('/loader.swf\?VideoIDS=([0-9A-Za-z=]+)/', $this->url, $match);
		if(count($match) >= 2)
			$this->vid = $match[1];
		preg_match('/player.youku.com\/embed\/([a-zA-Z0-9=]+)/', $this->url, $match);
		if(count($match) >= 2)
			$this->vid = $match[1];
	}
	public function getVideo($u=null)
	{
		if(isset($u))
			$this->url = $u;
		$this->getVidFromURL();
		$api_url = 'http://play.youku.com/play/get.json?vid='.$this->vid.'&ct=10';
        $api12_url = 'http://play.youku.com/play/get.json?vid='.$this->vid.'&ct=12';
		$meta = json_decode($this->http->get($api_url));
		$meta_12 = json_decode($this->http->get($api12_url));
		$data = $meta->data;
		$data_12 = $meta_12->data;
		if(!isset($data->stream))
			return null;
		$video = new YoukuVideoResult();
		$video->title = $data->video->title;
		$ep = $data_12->security->encrypt_string;
		$ip = $data_12->security->ip;
		$audio_lang = $data->stream[0]->audio_lang;
		$streams = array();
		foreach($data->stream as $stream)
		{
			$stream_id = $stream->stream_type;
			if(array_key_exists($stream_id, self::$stream_types) && $stream->audio_lang == $audio_lang)
			{
				if(array_key_exists('alias-of', self::$stream_types[$stream_id]))
					$stream_id = self::$stream_types[$stream_id]['alias-of'];
				if(!array_key_exists($stream_id, $video->urls))
				{
					$video->urls[$stream_id]['type'] = self::$stream_types[$stream_id]['container'];
					$video->urls[$stream_id]['quality'] = self::$stream_types[$stream_id]['video_profile'];
					$video->urls[$stream_id]['size'] = $stream->size;
					$video->urls[$stream_id]['hasUrl'] = false;
					$streams[$stream_id] = array($stream->segs);
				}
				else
				{
					$video->urls[$stream_id]['size'] += $stream->size;
					array_push($streams[$stream_id], $stream->segs);
				}
			}
		}
		$e_code = $this->decode(self::$f_code_1, $ep);
		$temp = explode("_", $e_code);
		$sid = $temp[0];
		$token = $temp[1];
		
		$stream_ids = array('hd3', 'hd2', 'mp4', 'flvhd', 'flv', '3gphd');
		foreach($stream_ids as $stream_id)
		{
			try{
				$ksegs = array();
				if(!array_key_exists($stream_id, $streams))
					continue;
				$seglist = $streams[$stream_id];
				foreach($seglist as $segs)
				{
					$seg_count = count($segs);
					for($no=0;$no<$seg_count;$no++)
					{
						$k = $segs[$no]->key;
						$fileid = $segs[$no]->fileid;
						if($k == -1)
						{
							//video requires payment
							break;
						}
						$tempep = $this->gen_ep($fileid, $sid, $token);
						$u = 'http://k.youku.com/player/getFlvPath/sid/'.$sid.'_00/st/'.$video->urls[$stream_id]['type'].'/fileid/'.$fileid."?ctype=12&ev=1&oip=$ip&token=$token&yxon=1&K=".urlencode($k)."&ep=".urlencode($tempep);
						$contents = json_decode($this->http->get($u));
						foreach($contents as $content)
							array_push($ksegs, $content->server);	
					}
				}
				$video->urls[$stream_id]['hasUrl'] = true;
			}
			catch(Exception $e)
			{
				echo $e." Error occurred<br>\n";
			}
			$video->urls[$stream_id]['segs'] = $ksegs;
			break;
		}
		return $video;
	}
	private function gen_ep($fileid, $sid, $token)
	{
		$ep = $this->decode(self::$f_code_2, base64_encode($sid.'_'.$fileid.'_'.$token));
		return base64_encode($ep);
	}
	private function decode($key, $cipher_64)
	{
		$cipher_decode = base64_decode($cipher_64);
		$cipher = array();
		foreach(str_split($cipher_decode) as $c)
			$cipher[] = ord($c);
		$f = 0;
		$h = 0;
		$b = range(0, 255);
		$result = '';
		while($h < 256)
		{
			$f = ($f + $b[$h] + ord($key[$h % strlen($key)])) % 256;
			$temp = $b[$f];
			$b[$f] = $b[$h];
			$b[$h] = $temp;
			$h++;
		}
		$q = 0;
		$f = 0;
		$h = 0;
		while($q < count($cipher))
		{
			$h = ($h + 1) % 256;
			$f = ($f + $b[$h]) % 256;
			$temp = $b[$f];
			$b[$f] = $b[$h];
			$b[$h] = $temp;
			$result = $result.chr($cipher[$q] ^ $b[($b[$h] + $b[$f]) % 256]);
			$q++;
		}
		return $result;
	}
}
?>
