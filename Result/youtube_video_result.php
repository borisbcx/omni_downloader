<?php
include_once(__DIR__.'/video_result.php');
class YoutubeVideoResult extends VideoResult{
	public $isPlayList;
	public static $definition_types = array("3072p", "1080p", "720p", "480p", "360p", "270p", "240p", "144p", "best");
	//structure of urls:
	//type:	
	//mime:
	//url:
	//type:
	//video_encoding:
	//audio_encoding:
	//quality:
	function __construct()
	{
		parent::__construct();
		$this->isPlayList = false;
	}
	public function addPiece($def, $piece)
	{
		if(!$this->isPieceExist($def))
			$this->urls[$def] = array();
		array_push($this->urls[$def], $piece);
	}
	public function isPieceExist($def)
	{
		return array_key_exists($def, $this->urls);
	}
	//TODO: retrive different video types
	public function getVideoURL($definition, $type="all")
	{
		if(count($this->urls) == 0)
			return null;
		if(!in_array($definition, self::$definition_types))
		{
			//if not a valid definition string, return one video that has type "type"
			//if not found, return null
			foreach($this->urls as $container)
				foreach(array_reverse($container) as $v)
					if($v["type"] == $type || $type == "all")
						return $v;
			return null;
		}
		else if($definition != "best" && array_key_exists($definition, $this->urls))
		{
			//if definition specified, return a video that has type "type"
			//if not found, return null
			foreach($this->urls[$definition] as $v)
				if($v["type"] == $type || $type == "all")
					return $v;
			return null;
		}
		else
		{
			//if definition is "best", return the best picture that has type "type"
			for($i=0;$i<count(self::$definition_types)-1;$i++)
			{
				$cur_definition = self::$definition_types[$i];
				if(array_key_exists($cur_definition, $this->urls))
					foreach(array_reverse($this->urls[$cur_definition]) as $v)
						if($v["type"] == $type || $type == "all")
							return $v;
			}
			return null;
		}
	}
}
?>