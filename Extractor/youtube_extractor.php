<?php
/***************************************************
* this is based on the python code of https://github.com/soimort/you-get
* this class is a wrapper of the extractor for youtube videoes
* Author: Chongxi Bao
*/
include_once(__DIR__."/extractor.php");
include_once(__DIR__."/../Utility/http.php");
include_once(__DIR__."/../Result/youtube_video_result.php");
/* 
*  YouTube media encoding options, in descending quality order.
*  http://en.wikipedia.org/wiki/YouTube#Quality_and_codecs. Retrieved July 17, 2014.
*
*/
class YoutubeExtractor extends Extractor{
	private $transferSubtitle;
	public static $stream_types = array(
		'itag=38' => array('container'=> 'MP4', 'video_resolution'=> '3072p', 'video_encoding'=> 'H.264', 'video_profile'=> 'High', 'video_bitrate'=> '3.5-5', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '192'),
		'itag=85' => array('container'=> 'MP4', 'video_resolution'=> '1080p', 'video_encoding'=> 'H.264', 'video_profile'=> '3D', 'video_bitrate'=> '3-4', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '192'),
		'itag=46' => array('container'=> 'WebM', 'video_resolution'=> '1080p', 'video_encoding'=> 'VP8', 'video_profile'=> '', 'video_bitrate'=> '', 'audio_encoding'=> 'Vorbis', 'audio_bitrate'=> '192'),
		'itag=37' => array('container'=> 'MP4', 'video_resolution'=> '1080p', 'video_encoding'=> 'H.264', 'video_profile'=> 'High', 'video_bitrate'=> '3-4.3', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '192'),
		'itag=102' => array('container'=> 'WebM', 'video_resolution'=> '720p', 'video_encoding'=> 'VP8', 'video_profile'=> '3D', 'video_bitrate'=> '', 'audio_encoding'=> 'Vorbis', 'audio_bitrate'=> '192'),
		'itag=45' => array('container'=> 'WebM', 'video_resolution'=> '720p', 'video_encoding'=> 'VP8', 'video_profile'=> '', 'video_bitrate'=> '2', 'audio_encoding'=> 'Vorbis', 'audio_bitrate'=> '192'),
		'itag=84' => array('container'=> 'MP4', 'video_resolution'=> '720p', 'video_encoding'=> 'H.264', 'video_profile'=> '3D', 'video_bitrate'=> '2-3', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '192'),
		'itag=22' => array('container'=> 'MP4', 'video_resolution'=> '720p', 'video_encoding'=> 'H.264', 'video_profile'=> 'High', 'video_bitrate'=> '2-3', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '192'),
		'itag=120' => array('container'=> 'FLV', 'video_resolution'=> '720p', 'video_encoding'=> 'H.264', 'video_profile'=> 'Main@L3.1', 'video_bitrate'=> '2', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '128'), # Live streaming only
		'itag=44' => array('container'=> 'WebM', 'video_resolution'=> '480p', 'video_encoding'=> 'VP8', 'video_profile'=> '', 'video_bitrate'=> '1', 'audio_encoding'=> 'Vorbis', 'audio_bitrate'=> '128'),
		'itag=35' => array('container'=> 'FLV', 'video_resolution'=> '480p', 'video_encoding'=> 'H.264', 'video_profile'=> 'Main', 'video_bitrate'=> '0.8-1', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '128'),
		'itag=101' => array('container'=> 'WebM', 'video_resolution'=> '360p', 'video_encoding'=> 'VP8', 'video_profile'=> '3D', 'video_bitrate'=> '', 'audio_encoding'=> 'Vorbis', 'audio_bitrate'=> '192'),
		'itag=100' => array('container'=> 'WebM', 'video_resolution'=> '360p', 'video_encoding'=> 'VP8', 'video_profile'=> '3D', 'video_bitrate'=> '', 'audio_encoding'=> 'Vorbis', 'audio_bitrate' => '128'),
		'itag=43' => array('container' => 'WebM', 'video_resolution'=> '360p', 'video_encoding'=> 'VP8', 'video_profile'=> '', 'video_bitrate'=> '0.5', 'audio_encoding'=> 'Vorbis', 'audio_bitrate'=> '128'),
		'itag=34' => array('container' => 'FLV', 'video_resolution'=> '360p', 'video_encoding'=> 'H.264', 'video_profile'=> 'Main', 'video_bitrate'=> '0.5', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '128'),
		'itag=82' => array('container'=> 'MP4', 'video_resolution'=> '360p', 'video_encoding'=> 'H.264', 'video_profile'=> '3D', 'video_bitrate'=> '0.5', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '96'),
		'itag=18' => array('container'=> 'MP4', 'video_resolution'=> '270p', 'video_encoding'=> 'H.264', 'video_profile'=> 'Baseline', 'video_bitrate'=> '0.5', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '96'),
		'itag=6' => array('container'=> 'FLV', 'video_resolution'=> '270p', 'video_encoding'=> 'Sorenson H.263', 'video_profile'=> '', 'video_bitrate'=> '0.8', 'audio_encoding'=> 'MP3', 'audio_bitrate'=> '64'),
		'itag=83' => array('container'=> 'MP4', 'video_resolution'=> '240p', 'video_encoding'=> 'H.264', 'video_profile'=> '3D', 'video_bitrate'=> '0.5', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '96'),
		'itag=18' => array('container'=> '3GP', 'video_resolution'=> '240p', 'video_encoding'=> 'MPEG-4 Visual', 'video_profile'=> '', 'video_bitrate'=> '0.5', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> ''),
		'itag=5' => array('container'=> 'FLV', 'video_resolution'=> '240p', 'video_encoding'=> 'Sorenson H.263', 'video_profile'=> '', 'video_bitrate'=> '0.25', 'audio_encoding'=> 'MP3', 'audio_bitrate'=> '64'),
		'itag=36' => array('container'=> '3GP', 'video_resolution'=> '240p', 'video_encoding'=> 'MPEG-4 Visual', 'video_profile'=> 'Simple', 'video_bitrate'=> '0.175', 'audio_encoding'=> 'AAC', 'audio_bitrate'=> '36'),
		'itag=17' => array('container'=> '3GP', 'video_resolution'=> '144p', 'video_encoding'=> 'MPEG-4 Visual', 'video_profile'=> 'Simple', 'video_bitrate'=> '0.05', 'audio_encoding'=> 'AAC', 'audio_bitrate' => '24'),
	);
	function __construct($u="")
	{
		parent::__construct($u);
		$this->http->setFollowLocation(true);
		$this->transferSubtitle = false;
	}
	function __destruct()
	{
		
	}
	//this function gets signature based on $s
	private function getSignature($file, $s)
	{
		$file = str_replace('\n', ' ', $file);
		preg_match('/"signature",([$\w]+)\(\w+\.\w+\)/', $file, $match);
		$f1 = $match[1];
		$f1quote = preg_quote($f1);
		preg_match("/function $f1quote(\(\w+\)\{[^\{]+\})/", $file, $match);
		if(count($match) < 2)
			preg_match("/\W$f1quote=function(\(\w+\)\{[^\{]+\})/", $file, $match);
		$f1def = $match[1];
		$f1def = preg_replace('/([$\w]+\.)([$\w]+\(\w+,\d+\))/', '\2', $f1def);
		//add assignment statements
		$f1def = preg_replace('/([$\w]+)\((\w+)(,\d+)\)/', '\2=\1(\2\3)', $f1def);
		$f1def = 'function '.$f1.$f1def;
		$code = tr_js($f1def);
		preg_match_all('/([$\w]+)\(\w+,\d+\)/', $f1def, $match);
		$f2s = array();
		foreach($match[1] as $r)
			if(!array_key_exists($r, $f2s))
				$f2s[$r] = $r;
		foreach($f2s as $f2)
		{
			$f2quote = preg_quote($f2);
			preg_match("/[^$\w]$f2quote:function(\(\w+,\w+\))(\{[^\{\}]+\})/", $file, $match);
			if(count($match) < 3)
				preg_match("/[^$\w]$f2quote:function(\(\w+\))(\{[^\{\}]+\})/", $file, $match);
			$f2 = preg_replace('/(\W)(as|if|in|is|or)\(/', '\1_\2(', $f2);
			$f2 = preg_replace('/\$/', '_dollar', $f2);
			$f2def = "function $f2".$match[1].$match[2]."\n";
			$code = $code."\n".tr_js($f2def);
		}
		$code = str_replace('}', ';}', $code);
		//add return statements
		$code = preg_replace('/\((\w+)\)(\{[^\{\}]+)\}/', '(\1)\2return \1;}', $code);
		$code = preg_replace('/\((\w+)(,\w+)\)(\{[^\{\}]+)\}/', '(\1\2)\3return \1;}', $code);
		//anything before = or should be a variable
		$code = preg_replace('/(\w+\s*)([;=])/', '\$\1\2', $code);
		//anything after ( , [ is possibly a variable
		$code = preg_replace('/(\s*[\[\(,]\s*)(\w+)/', '\1\$\2', $code);
		//with the exception that it is a pure number 
		$code = preg_replace('/\$(\d+)/', '\1', $code);
		//anything before [ is a variable
		$code = preg_replace('/(\w+)\[/', '\$\1[', $code);
		//anything after = and is a whole word is a variable
		$code = preg_replace('/(=\s*)(\w+)\s*;/', '\1\$\2;', $code);
		$code = $code."\n\$signature= $f1(\$s);";

		eval($code);
		return $signature;
	}
	//this function is a helper function used in getSignature
	private function tr_js($code)
	{
		$code = preg_replace('/(\W)(as|if|in|is|or)\(/', '\1_\2(', $code);
		$code = preg_replace('/\$/', '_dollar', $code);
		$code = preg_replace('/var\s+/', '', $code);
		$code = preg_replace('/(\w+).split\(""\)/', 'str_split(\1)', $code);
		$code = preg_replace('/(\w+).reverse\(\)/', '\1=array_reverse(\1)', $code);
		$code = preg_replace('/(\w+).join\(""\)/', 'join("", \1)', $code);
		$code = preg_replace('/(\w+).length/', 'count(\1)', $code);
		$code = preg_replace('/(\w+).slice\((\w+)\)/', 'array_slice(\1, \2)', $code);
		$code = preg_replace('/(\w+).splice\((\w+),(\w+)\)/', 'array_splice(\1, \2, \3)', $code);
		return $code;
	}	
	private function getVidFromURL()
	{
		preg_match('/embed\?v=([^\/]+)/', $this->url, $match);
		if(count($match) >= 2)
			$this->vid = $match[1];
		preg_match('/watch\?v=([^\/]+)/', $this->url, $match);
		if(count($match) >= 2)
			$this->vid = $match[1];
		preg_match('/v\?v=([^\/]+)/', $this->url, $match);
		if(count($match) >= 2)
			$this->vid = $match[1];
		preg_match('/youtu\.be\/\?v=([^\/]+)/', $this->url, $match);
		if(count($match) >= 2)
			$this->vid = $match[1];		
	}
	//return json ytplayerObject
	public function parseVideoPage($v)
	{
		//parse video page
		try{
			$videoPage = $this->http->get("https://www.youtube.com/watch?v=".$v);
			preg_match('/ytplayer.config\s*=\s*([\s\S]+);ytplayer.load/', $videoPage, $match);
			$ytplayerObj = json_decode($match[1]);
			return $ytplayerObj;
		}
		catch(Exception $e)
		{
			preg_match('/class="message">([^<]+)</', $videoPage, $match);
			if(count($match) >= 2)
				throw new Exception("Parse Video Page Error : ".trim($match[1]));
			else
				throw new Exception("Parse Video Page Error : unknown");
			return null;
		}
	}
	public function getVideo($u=null)
	{
		$youtubeVideo = new YoutubeVideoResult();
		if(!empty($u))
		{
			$this->url = $u;
			$this->getVidFromURL();
		}
		if($this->vid == "")
			$this->getVidFromURL();
		$result = $this->http->get("https://www.youtube.com/get_video_info?video_id=".$this->vid);
		parse_str($result, $videoInfo);
		if(!array_key_exists("status", $videoInfo))
		{
			//echo "unknown status";
			return "";
		}
		if($videoInfo["status"] == "ok")
		{
			if(!array_key_exists("use_cipher_signature", $videoInfo) || $videoInfo["use_cipher_signature"] == "False")
			{
				$youtubeVideo->title = $videoInfo["title"];
				$streamList = explode(",",$videoInfo["url_encoded_fmt_stream_map"]);
				//parse video page
				try{
					$ytplayerObj = $this->parseVideoPage($this->vid);
					$player = 'https://www.youtube.com/'.$ytplayerObj->{'assets'}->{'js'};
				}
				catch(Exception $e)
				{
					$player = '';
					//echo $e->getMessage();
				}
			}
			else
			{
				//parse video page
				try{
					$ytplayerObj = $this->parseVideoPage($this->vid);
					$player = 'https://www.youtube.com'.$ytplayerObj->{'assets'}->{'js'};
					$youtubeVideo->title = $ytplayerObj->{'args'}->{'title'};
					$streamList = explode(",",$ytplayerObj->{'args'}->{'url_encoded_fmt_stream_map'});
				}
				catch(Exception $e)
				{
					$player = '';
					//echo $e->getMessage();
				}			
			}
		}
		else if($videoInfo["status"] == "fail")
		{
			if($videoInfo["errorcode"] == '150')
			{
				//parse video page
				try{
					$ytplayerObj = $this->parseVideoPage($this->vid);
					if(array_key_exists("title", $ytplayerObj->{'args'}))
					{
					    // 150 Restricted from playback on certain sites
						// Parse video page instead
						$player = 'https://www.youtube.com'.$ytplayerObj->{'assets'}->{'js'};
						$youtubeVideo->title = $ytplayerObj->{'args'}->{'title'};
						$streamList = explode(",",$ytplayerObj->{'args'}->{'url_encoded_fmt_stream_map'});
					}
					else
						//echo "The uploader has not made this video available in your country.";
						echo "";
				}
				catch(Exception $e)
				{
					//echo $e->getMessage();
				}
			}
			else if($videoInfo["errorcode"] == '100')
				//echo "This video does not exist.";
				echo "";
			else 
				// echo "Received errorcode ".$videoInfo["errorcode"];
				echo "";
		}
		else
		{
			//echo "Invalid status";
		}
		if($player != "")
			$file = $this->http->get($player);
		else
			$file = "";
		$youtubeVideo->id = $this->vid;
		//generate stream list
		foreach($streamList as $s)
		{
			//get meta data
			parse_str($s, $meta);
			$reso = $this::$stream_types["itag=".$meta["itag"]]["video_resolution"];
			if(!array_key_exists($reso, $youtubeVideo->urls))
				$youtubeVideo->urls[$reso] = array();
			$item = array();
			$item["url"] = $meta["url"];
			$item["mime"] = $meta["type"];
			if(array_key_exists("s", $meta))
			{
				$item["s"] = $meta["s"];
				$item["url"] = $meta["url"]."&signature=".$this->getSignature($file, $meta["s"]);
			}
			if(array_key_exists("sig", $meta))
				$item["sig"] = $meta["sig"];
			$item["type"] = strtolower($this::$stream_types["itag=".$meta["itag"]]["container"]);
			$item["video_encoding"] = $this::$stream_types["itag=".$meta["itag"]]["video_encoding"];
			$item["audio_encoding"] = $this::$stream_types["itag=".$meta["itag"]]["audio_encoding"];
			$item["quality"] = $reso;
			array_push($youtubeVideo->urls[$reso], $item);
		}
		//prepare caption tracks
		if($this->transferSubtitle && isset($ytplayerObj) && $ytplayerObj != null)
		{
			try{
				$caption_tracks = $ytplayerObj->{'args'}->{'caption_tracks'};
				if(!is_array($caption_tracks))
					$caption_tracks = array($caption_tracks);
				$lang = "";
				foreach($caption_tracks as $ct)
				{
					parse_str($ct, $ct_result);
					if(array_key_exists("lc", $ct_result) && $lang == "")
						$lang = $ct_result["lc"];
					if(array_key_exists("v", $ct_result) && substr($ct_result["v"], 0, 1) != ".")
						$lang = $ct_result["v"];
					if(array_key_exists("u", $ct_result))
						$tts_url = $ct_result["u"];
					$tts_xml = $this->http->get($tts_url);
					//parse subtitle;
					$p = xml_parser_create();
					$vals = array();
					$index = array();
					xml_parse_into_struct($p, $tts_xml, $vals, $index);
					xml_parser_free($p);
					$srt = "";
					$seq = 0;
					foreach($vals as $v)
					{
						if($v["tag"] != "TEXT" || !array_key_exists("value", $v) || !array_key_exists("attributes", $v))
							continue;
						$start = $v["attributes"]["START"];
						if(array_key_exists("DUR", $v["attributes"]))
							$dur = $v["attributes"]["DUR"];
						else
							$dur = 1;
						$seq++;
						$finish = $start + $dur;
						$sec = $start % 60;
						$min = $start / 60;
						$hour = floor($min / 60);
						$min = floor($min % 60);
						$sec = $start - $hour * 3600 - $min * 60;
						$startStr = sprintf("%02d:%02d:%06.3f", $hour, $min, $sec);
						str_replace(".", ",", $startStr);
						$sec = $finish % 60;
						$min = $finish / 60;
						$hour = floor($min / 60);
						$min = floor($min % 60);
						$sec = $finish - $hour * 3600 - $min * 60;
						$finishStr = sprintf("%02d:%02d:%06.3f", $hour, $min, $sec);
						str_replace(".", ",", $finishStr);						
						$srt = $srt.$seq."\n";
						$srt = $srt.$startStr." --> ".$finishStr."\n";
						$srt = $srt.$v["value"]."\n\n";
					}
					$youtubeVideo->captions[$lang] = $srt;
				}
			}
			catch (Exception $e)
			{
				
			}
		}
		//prepare DASH streams
		if(isset($ytplayerObj) && $ytplayerObj != null)
		{
			if(isset($ytplayerObj->args->dashmpd))
			{
				// $dashxml = $this->http->get($ytplayerObj->args->dashmpd);
				// $p = xml_parser_create();
				// $vals = array();
				// $index = array();
				// xml_parse_into_struct($p, $dashmpd, $vals, $index);
				// xml_parser_free($p);
				// print_r($vals);
			}
			else
			{
				if(!isset($player) || $player == "")
					return $youtubeVideo;
				if(isset($ytplayerObj->args->adaptive_fmts))
				{
					$streamList = explode(",",$ytplayerObj->args->adaptive_fmts);
					foreach($streamList as $s)
					{
						//get meta data
						parse_str($s, $meta);
						if(!array_key_exists($meta["itag"], self::$stream_types))
						{
							if(!array_key_exists('quality_label', $meta))
								continue;
							$reso = $meta["quality_label"];
							if(!array_key_exists($reso, $youtubeVideo->urls))
								$youtubeVideo->urls[$reso] = array();
							$item = array();
							$item["url"] = $meta["url"];
							$item["mime"] = $meta["type"];
							if(array_key_exists("s", $meta))
							{
								$item["s"] = $meta["s"];
								$item["url"] = $meta["url"]."&signature=".$this->getSignature($file, $meta["s"]);
							}
							if(array_key_exists("sig", $meta))
								$item["sig"] = $meta["sig"];
							$item["quality"] = $reso;
							preg_match('/(video|audio)\/(\w+)/', urldecode($meta["type"]), $match);
							$item["type"] = strtolower($match[2]); 
							array_push($youtubeVideo->urls[$reso], $item);							
						}
						else
						{
							$reso = $this::$stream_types["itag=".$meta["itag"]]["video_resolution"];
							if(!array_key_exists($reso, $youtubeVideo->urls))
								$youtubeVideo->urls[$reso] = array();
							$item = array();
							$item["url"] = $meta["url"];
							$item["mime"] = $meta["type"];
							if(array_key_exists("s", $meta))
							{
								$item["s"] = $meta["s"];
								$item["url"] = $meta["url"]."&signature=".$this->getSignature($file, $meta["s"]);
							}
							if(array_key_exists("sig", $meta))
								$item["sig"] = $meta["sig"];
							$item["type"] = strtolower($this::$stream_types["itag=".$meta["itag"]]["container"]);
							$item["video_encoding"] = $this::$stream_types["itag=".$meta["itag"]]["video_encoding"];
							$item["audio_encoding"] = $this::$stream_types["itag=".$meta["itag"]]["audio_encoding"];
							$item["quality"] = $reso;
							array_push($youtubeVideo->urls[$reso], $item);
						}
					}
				}
			}
		}
		return $youtubeVideo;
	}
}
?>