<?php
include_once(__DIR__.'/../Utility/http.php');
include_once(__DIR__.'/../Result/xiami_result.php');
include_once(__DIR__.'/../Extractor/extractor.php');
class XiamiExtractor extends Extractor{
	function __construct()
	{
		$this->http = new HTTP();
		$this->http->setUserAgent('Mozilla/5.0 (X11; Linux x86_64; rv:13.0) Gecko/20100101 Firefox/13.0');
		$this->http->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
		$this->http->setHeader('Accept-Charset', 'UTF-8,*;q=0.5');
		$this->http->setHeader('Accept-Language', 'en-US,en;q=0.8');
		$this->http->setReferer('www.xiami.com');
	}
	function __destruct()
	{
		
	}
	public function getCollectIDFromURL($url)
	{
		preg_match('/www.xiami.com\/collect\/([0-9]+)$/', $url, $match);
		return $match[1];
	}
	public function getAlbumIDFromURL($url)
	{
		preg_match('/www.xiami.com\/album\/([0-9]+)$/', $url, $match);
		if(count($match) > 1)
			return $match[1];
		// echo microtime(true)."<br>";
		$file = $this->http->get($url);
		// echo microtime(true)."<br>";
		preg_match('/rel="canonical" href="http:\/\/www.xiami.com\/album\/([0-9]+)"/', $file, $match);
		return $match[1];		
	}
	public function download_showcollect($url)
	{
		$cid = $this->getCollectIDFromURL($url);
		$json_url = "http://www.xiami.com/song/playlist/id/$cid/type/3/cat/json";
		return $this->parseJson($json_url);
	}
	public function getSongIDFromURL($url)
	{
		preg_match('/www.xiami.com\/song\/([0-9]+)$/', $url, $match);
		if(count($match) > 1)
			return $match[1];			
		$file = $this->http->get($url);
		preg_match('/rel="canonical" href="http:\/\/www.xiami.com\/song\/([0-9]+)"/', $file, $match);
		return $match[1];
	}
	public function download_song($url)
	{
		$sid = $this->getSongIDFromURL($url);
		$json_url = "http://www.xiami.com/song/playlist/id/$sid/type/0/cat/json";
		return $this->parseJson($json_url);		
	}
	public function download_album($url)
	{
		$aid = $this->getAlbumIDFromURL($url);
		$json_url = "http://www.xiami.com/song/playlist/id/$aid/type/1/cat/json";
		return $this->parseJson($json_url);
	}
	private function parseJson($json_url)
	{
		$content = $this->http->get($json_url);
		$json = json_decode($content);
		if(!isset($json->status) || !$json->status)
			return null;
		$tracks = $json->data->trackList;
		$audio = new XiamiResult();
		foreach($tracks as $track)
		{
			if(isset($track->songId))
				$song_id = $track->songId;
			else
				$song_id = "";
			if($song_id == "")
				continue;
			$item = array();
			if(isset($track->songName))
				$song_title = $track->songName;
			else
				$song_title = "";
			$item['title'] = $song_title;
			if(isset($track->album_name))
				$album_name = $track->album_name;
			else
				$album_name = "";
			$item['album'] = $album_name;			
			if(isset($track->singers))
				$song_singer = $track->singers;
			else
				$song_singer = "";
			$item['artist'] = $song_singer;
			if(isset($track->location))
			{
				$location = $track->location;
				$item['url'] = $this->location_dec($location);
			}
			else
				$location = "";
			if(isset($track->lyric))
			{
				$lyric = $track->lyric;
				$item['lyric'] = $lyric;
			}
			else
				$lyric = "";
			if(isset($track->album_pic))
				$item['thumbnail'] = $track->album_pic;
			$audio->addPiece('id='.$song_id, $item);
		}
		return $audio;
	}
	
	private function location_dec($str)
	{
		$head = intval(substr($str, 0, 1));
		$str = substr($str, 1);
		$rows = $head;
		$cols = intval(strlen($str) / $rows) + 1;
		$out = "";
		$full_row = strlen($str) % $head;
		for($c=0;$c<$cols;$c++)
			for($r=0;$r<$rows;$r++)
			{
				if($c == $cols-1 && $r>=$full_row)
					continue;
				if($r < $full_row)
					$char = $str[$r*$cols+$c];
				else
					$char = $str[$cols*$full_row+($r-$full_row)*($cols-1)+$c];
				$out = $out.$char;
			}
		return str_replace('^', '0', urldecode($out));
	}
}
?>
