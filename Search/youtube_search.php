<?php
include_once(__DIR__."/../Utility/http.php");
include_once(__DIR__."/../Result/youtube_video_result.php");

class YoutubeSearch
{
	private $keyword;
	private $http;
	private $order;
	private $hd;
	public static $order_values = array("date", "relevance", "rating", "title", "videoCount", "viewCount");
	private $duration;
	public static $apikey = "AIzaSyDUozltg6Ez80V-qfxpfmajRQnmyTwrk0k";
	private $type;
	private $resultspp;
	private $prevToken;
	private $nextToken;
	private $playlistid;
	private $searchType;
	function __construct()
	{
		$this->http = new HTTP();
		$this->order = "relevance";
		$this->hd = "any";
		$this->duration = "any";
		$this->resultspp = 20;
		$this->prevToken = "";
		$this->nextToken = "";
		$this->playlistid = "";
		$this->searchType = "search";
	}
	function __destruct()
	{
		
	}
	function setFilterLength($l, $u=null)
	{
		if($l > $u || $l<0 || $u<0)
			$this->duration = "any";
		else if($l >= 20)
			$this->duration = "long";
		else if(isset($u) && $u<= 20 && $l>=4)
			$this->duration = "medium";
		else if(isset($u) && $u<4)
			$this->duration = "short";
		else
			$this->duration = "any";			
	}
	function setPageSize($p)
	{
		if($p <= 50 && $p > 0)
			$this->resultspp = $p;
	}
	function setFilterHD($w)
	{
		if($w > 0)
			$this->hd = "high";
		else if($w == 0)
			$this->hd = "any";
		else
			$this->hd = "standard";
	}
	function setOrder($o)
	{
		if(in_array($o, self::$order_values))
			$this->order = $o;
	}
	public function getNextPage()
	{
		if($this->nextPageToken != "")
		{
			if($this->searchType == "search")
				return $this->search($this->nextPageToken);
			else
				return $this->getPlayList("", $this->nextPageToken);
		}
		else
			return null;
		
	}
	public function getPrevPage()
	{
		if($this->prevPageToken != "")
		{
			if($this->searchType == "search")
				return $this->search($this->prevPageToken);
			else
				return $this->getPlayList("", $this->prevPageToken);
		}
		else
			return null;
	}
	public function searchVideo($k = "")
	{
		if($k != "")
			$this->keyword = $k;
		$this->searchType = "search";
		$this->type = "video";
		return $this->search();
	}
	public function searchPlaylist($k = "")
	{
		if($k != "")
			$this->keyword = $k;
		$this->searchType = "search";		
		$this->type = "playlist";
		return $this->search();
	}	
	public function searchAll($k = "")
	{
		if($k != "")
			$this->keyword = $k;
		$this->searchType = "search";		
		$this->type = "video,playlist";
		return $this->search();
	}	
	private function search($pageToken=null)
	{
		$queryString = "https://www.googleapis.com/youtube/v3/search?part=snippet&q=".urlencode($this->keyword)."&order=".$this->order."&maxResults=".$this->resultspp."&key=".self::$apikey;
		if($this->type == "video")
			$queryString = $queryString."&videoDefinition=".$this->hd."&videoDuration=".$this->duration."&type=video";
		else
			$queryString = $queryString."&type=".$this->type;
		if(isset($pageToken))
			$queryString = $queryString."&pageToken=".$pageToken;
		$result = $this->http->get($queryString);
		$resultobj = json_decode($result);
		if(isset($resultobj->error))
			return null;
		if(isset($resultobj->nextPageToken))
			$this->nextPageToken = $resultobj->nextPageToken;
		if(isset($resultobj->prevPageToken))
			$this->prevPageToken = $resultobj->prevPageToken;
		$items = $resultobj->items;
		$resultArray = array();
		foreach($items as $item)
		{
			if($item->snippet->title == "Deleted video" || $item->snippet->title == "Private video")
				continue;
			try{
				if($item->id->kind == "youtube#playlist")
				{
					$youtubeVideo = new YoutubeVideoResult();
					$youtubeVideo->id = $item->id->playlistId;
					$youtubeVideo->isPlayList = true;
				}
				else if($item->id->kind == "youtube#video")
				{
					$youtubeVideo = new YoutubeVideoResult();
					$youtubeVideo->id = $item->id->videoId;
				}
				else
					continue;
				$youtubeVideo->title = $item->snippet->title;
				$youtubeVideo->description = $item->snippet->description;
				foreach($item->snippet->thumbnails as $key=>$val)
					$youtubeVideo->thumbnails[$key] = $val->url;
				array_push($resultArray, $youtubeVideo);
			}
			catch (Exception $e)
			{

			}
		}
		return $resultArray;
	}	
	public function getPlayList($pid="", $pageToken=null)
	{
		if($pid != "")
			$this->playlistid = $pid;
		$this->searchType = "playlist";
		if(isset($pageToken))
			$result = $this->http->get("https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=".$this->playlistid."&key=".self::$apikey."&maxResults=".$this->resultspp."&pageToken=".$pageToken);
		else
			$result = $this->http->get("https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=".$this->playlistid."&key=".self::$apikey."&maxResults=".$this->resultspp);
		$resultobj = json_decode($result);
		if(isset($resultobj->error))
			return null;
		if(isset($resultobj->nextPageToken))
			$this->nextPageToken = $resultobj->nextPageToken;
		if(isset($resultobj->prevPageToken))
			$this->prevPageToken = $resultobj->prevPageToken;
		$items = $resultobj->items;
		$resultArray = array();
		foreach($items as $item)
		{
			if($item->snippet->title == "Deleted video" || $item->snippet->title == "Private video")
				continue;
			try{
				if($item->snippet->resourceId->kind == "youtube#playlist")
				{
					$youtubeVideo = new YoutubeVideoResult();
					$youtubeVideo->id = $item->snippet->resourceId->playlistId;
					$youtubeVideo->isPlayList = true;
				}
				else if($item->snippet->resourceId->kind == "youtube#video")
				{
					$youtubeVideo = new YoutubeVideoResult();
					$youtubeVideo->id = $item->snippet->resourceId->videoId;
				}
				else
					continue;
				$youtubeVideo->title = $item->snippet->title;
				$youtubeVideo->description = $item->snippet->description;
				foreach($item->snippet->thumbnails as $key=>$val)
					$youtubeVideo->thumbnails[$key] = $val->url;
				array_push($resultArray, $youtubeVideo);
			}
			catch (Exception $e)
			{
			}
		}
		return $resultArray;		
	}
}
?>