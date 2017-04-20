<?php
include_once(__DIR__.'/video_result.php');
class YoukuVideoResult extends VideoResult{
	public static $definition_types = array("hd3", "hd2", "mp4", "flvhd", "flv", "3gphd");
	function __construct()
	{
		parent::__construct();
	}
	//TODO: retrive different video types
	public function getVideoURL($definition, $type="all")
	{
		if(count($this->urls) == 0)
			return null;
		if(!in_array($definition, self::$definition_types))
		{
			//if definition is "best", return the best picture that has type "type"
			for($i=0;$i<count(self::$definition_types)-1;$i++)
			{
				$cur_definition = self::$definition_types[$i];
				if(array_key_exists($cur_definition, $this->urls) && $this->urls[$cur_definition]['hasUrl'])
				{
					$v = $this->urls[$cur_definition];
					if(($type == "all" || $v["type"] == $type))
						return $v;
				}
			}
			return null;
		}
		else
		{
			//if definition specified, return a video that has type "type"
			//if not found, return null
			$v = $this->urls[$definition];
			if(($type == "all" || $v["type"] == $type) && $v['hasUrl'])
				return $v;
			return null;
		}
	}
}
?>