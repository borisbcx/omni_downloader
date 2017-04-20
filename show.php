<?php
include_once("Search/youtube_search.php");
include_once("Result/youtube_video_result.php");
include_once("Result/youku_video_result.php");
include_once("Result/xiami_result.php");
include_once("Extractor/youtube_extractor.php");
include_once("Extractor/youku_extractor.php");
include_once("Extractor/xiami_extractor.php");
?>
<?php
define ('VERSION', '0.5.0');
define ('UPDATE_LOG', "
更新日志：
<ul>
	<li>0.5.0</li>
	<ol>
		<li>加入了虾米网,优酷,youtube的支持</li>
	</ol>
<ul>
");
$VISITOR_MAX = 20;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
 <meta name="referrer" content="no-referrer" />
   <title>下载利器</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="fa/css/font-awesome.min.css">
	<script type="text/javascript" src="hdflvplayer/swfobject.js"></script>
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <script src="style.css"></script>
   <style>
	li.addrs {width:140px;display: inline-block; height:40px;margin:5px;border:1px solid #ccc;text-align:center;}
	.more {display: none;}
	a.showLink, a.hideLink {text-decoration: none;color: #36f;padding-left: 8px;}
	.form-element label {display: inline-block;width:100px;float:left;}
	.form-element {width:100%}
	.row:not(:last-child) {margin-bottom:20px;margin-top:20px;}	
	.row{align:center;}
	html, body {
		height:100%;
	}	
	body{
		/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#e9e9ce+0,fcfff4+47,e9e9ce+100 */
		background: #e9e9ce; /* Old browsers */
		background: -moz-linear-gradient(top,  #e9e9ce 0%, #fcfff4 47%, #e9e9ce 100%); /* FF3.6-15 */
		background: -webkit-linear-gradient(top,  #e9e9ce 0%,#fcfff4 47%,#e9e9ce 100%); /* Chrome10-25,Safari5.1-6 */
		background: linear-gradient(to bottom,  #e9e9ce 0%,#fcfff4 47%,#e9e9ce 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#e9e9ce', endColorstr='#e9e9ce',GradientType=0 ); /* IE6-9 */
	}
	input[type="text"] {
		border-top: 0;
		border-right: 0;
		border-left: 0;
		 -webkit-box-shadow: none;
		 box-shadow: none; 
	}
	.glyphicon-heart {
	  font-size: 30px;
		}
	.fa-star {
	  font-size: 30px;
	  color: #e67e22;
	}
	.fa-star-half-o {
	  font-size: 30px;
	  color: #e67e22;
	}
   }
</style>
<script type="text/javascript">
function showHide(shID) {
	if (document.getElementById(shID)) {
		if (document.getElementById(shID+'-show').style.display != 'none') {
			document.getElementById(shID+'-show').style.display = 'none';
			document.getElementById(shID).style.display = 'inline';
		}
		else {
			document.getElementById(shID+'-show').style.display = 'inline';
			document.getElementById(shID).style.display = 'none';
		}
	}
}
</script>
<script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
<h3>
<div class="container">
<form action=<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?> method="post" class="form-group">
		<div class="row">
		<div class="col-xs-6 col-md-8">
		<input type="text" onclick='javascript: this.value = ""' name="key" class="form-control input-lg" placeholder="请输入搜索关键词或者粘贴一个网址" value="<?php echo isset($_POST['key']) ? $_POST['key'] : $key;?>">
		</div>
		<div class="col-xs-3 col-md-2"><button type="submit" class="btn btn-primary btn-lg">获取下载链接</button></div>
		</div>
</form>
<?php
$key = isset($_POST['key']) ? $_POST['key'] : '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && $key != "") {
	//test xiami
	preg_match('/xiami.com\/([a-z]+)\//', $key, $match);	
	if(count($match) > 1)
	{
		$xe = new XiamiExtractor();
		if($match[1] == 'song')
			$result = $xe->download_song($key);
		else if($match[1] == 'album')
			$result = $xe->download_album($key);
		else if($match[1] == 'collect')
			$result = $xe->download_collect($key);
		else
		{
			echo '<div class="bg-danger text-center">';
			echo "输入不合法";	
			echo '</div>';
			exit();
		}
		$row_count = 0;
		echo '<div class="row">';
		echo '<div class="col-xs-10 text-justify"><ul>';
		foreach($result->urls as $row) {

			echo '<li><b><a href="'.$row['url'].'" class="text-primary" target="_blank">['.$row['artist'].'] '.$row['title'].'</a></b></li>';
			$row_count++;
		}
		echo '</ul></div></div>'; 
		if($row_count == 0)
		{
			echo '<div class="bg-danger text-center">';
			echo "搜索：<b>".$key.', 共0条结果';	
			echo '</div>';
			exit();
		}
		else
		{
			echo '<div class="bg-success text-center">';
			echo "共".$row_count."条结果,使用右键-->另存为下载";	
			echo '</div>';			
		}
		exit();
	}
	//test youtube
	preg_match('/youtube.com(\/)/', $key, $match);
	preg_match('/youtu.be(\/)/', $key, $match1);
	if(count($match) > 1 || count($match1) > 1)
	{
		$ye = new YoutubeExtractor();
		$result = $ye->getVideo($key);
		echo '<div class="row">';
		echo '<div class="col-xs-10 text-justify"><ul>';
		$v = $result->getVideoURL('best');
		if(isset($v))
		{
			echo '<ul><li><b><a href="'.$v['url'].'" class="text-primary" target="_blank">['.$v['quality'].'] '.$result->title.'</a></b></li></ul>';
			$row_count = 1;
		}
		else
			$row_count = 0;
		echo '</div></div>';
		if($row_count == 0)
		{
			echo '<div class="bg-danger text-center">';
			echo "没有符合的结果";	
			echo '</div>';
			exit();
		}
		else
		{
			echo '<div class="bg-success text-center">';
			echo "共".$row_count."条结果,使用右键-->另存为下载";	
			echo '</div>';			
		}
		exit();
	}
	//test youku
	preg_match('/youku.com(\/)/', $key, $match1);
	if(count($match) > 1 || count($match1) > 1)
	{
		$ye = new YoukuExtractor();
		$result = $ye->getVideo($key);
		echo '<div class="row">';
		echo '<div class="col-xs-10 text-justify"><ul>';
		$v = $result->getVideoURL('best');
		if(isset($v))
		{
			echo '<ul>';
			$row_count = 0;
			foreach($v["segs"] as $seg)
			{
				$row_count++;
				echo '<li><b><a href="'.$seg.'" class="text-primary" target="_blank">['.$v['quality'].'] '.$result->title.' -- Part '.$row_count.'/'.count($v["segs"]).'</a></b></li>';
			}
			echo '</ul>';
		}
		else
			$row_count = 0;
		echo '</div></div>';
				echo '</ul></div></div>'; 
		if($row_count == 0)
		{
			echo '<div class="bg-danger text-center">';
			echo '没有符合的结果';	
			echo '</div>';
			exit();
		}
		else
		{
			echo '<div class="bg-success text-center">';
			echo "共".$row_count."条结果.使用右键-->另存为下载。优酷使用了文件分片";	
			echo '</div>';			
		}
		exit();
	}
	//else, search youtube
	$ys = new YoutubeSearch();
	$ys->setPageSize(6);
	$result = $ys->searchVideo($key);
	$ye = new YoutubeExtractor();
	echo '<div class="row">';
	echo '<div class="col-xs-10 text-justify"><ul>';
	$row_count = 0;
	foreach($result as $row)
	{
		$ye->setVid($row->id);
		$vi = $ye->getVideo();
		$v = $vi->getVideoURL('best');
		if(!isset($v))
			continue;
		echo '<li><b><a href="'.$v['url'].'" class="text-primary" target="_blank">['.$v['quality'].'] '.$row->title.'</a></b></li>';
		$row_count++;		
	}
	echo '</ul></div></div>';
	if($row_count == 0)
	{
		echo '<div class="bg-danger text-center">';
		echo "没有符合的结果";	
		echo '</div>';
		exit();
	}
	else
	{
		echo '<div class="bg-success text-center">';
		echo "共".$row_count."条结果,使用右键-->另存为下载";	
		echo '</div>';			
	}
}
else
{
	echo '<div class="bg-success">';
	echo UPDATE_LOG;
	echo '</div></div>';
}	
?>
</div>
</h2>
</body>
</html>
