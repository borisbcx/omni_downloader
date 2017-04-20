<?php
include_once(__DIR__.'/video_result.php');
class XiamiResult extends VideoResult{
	public $isPlayList;
	function __construct()
	{
		parent::__construct();
		$this->isPlayList = false;
	}
	public function addPiece($def, $piece)
	{
		$this->urls[$def] = $piece;
	}
	//TODO: retrive different video types
	public function getVideoURL($definition, $type="mp4")
	{
		if(count($this->urls) == 0)
			return null;
		if(!in_array($definition, self::$definition_types))
		{
			//if not a valid definition string, return one video that has type "type"
			//if not found, return null
			foreach($this->urls as $container)
				foreach($container as $v)
					if($v["type"] == $type)
						return $v;
			return null;
		}
		else if($definition != "best" && array_key_exists($definition, $this->urls))
		{
			//if definition specified, return a video that has type "type"
			//if not found, return null
			foreach($this->urls[$definition] as $v)
				if($v["type"] == $type)
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
					foreach($this->urls[$cur_definition] as $v)
						if($v["type"] == $type)
							return $v;
			}
			return null;
		}
	}
}
?>