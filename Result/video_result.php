<?php
class VideoResult{
	public $title;
	public $id;
	public $description;
	//urls is an array, key is one of the values in $definition_types, value is an array of information about the video that this url points to
	public $urls;
	//thumbnails is an array. key is the size, value is the url for thumbnail pics
	public $thumbnails;
	//caption is an associate array. key is the language, value is the content of srt file
	public $captions;
	function __construct($title="", $id="")
	{
		$this->title = $title; 
		$this->id = $id;
		$this->description = "";
		$this->urls = array();
		$this->thumbnails = array();
		$this->size = 0;
		$this->captions = array();
	}
	function __destruct()
	{
		
	}

}
?>