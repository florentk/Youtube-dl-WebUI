<?php

class FileHandler
{
	private $config = [];
	private $videos_ext = ".{avi,mp4,flv}";
	private $musics_ext = ".{mp3,ogg,m4a}";

	public function __construct()
	{
		$this->config = require dirname(__DIR__).'/config/config.php';
	}

	public function listVideos()
	{
		$videos = [];

		if(!$this->outuput_folder_exists())
			return;

		$folder = dirname(__DIR__).'/'.$this->config["outputFolder"].'/';
		foreach(glob($folder.'*'.$this->videos_ext, GLOB_BRACE) as $file)
		{
			$video = [];
			$video["name"] = str_replace($folder, "", $file);
			$video["size"] = $this->to_human_filesize(filesize($file));
			$video["mtime"] = filemtime($file);
			
			$videos[] = $video;
		}

		return $videos;
	}

  static public function compareByTime($a, $b) {
    return $b["mtime"] - $a["mtime"];
  }

  public function lastVideos($n) 
  {

    $videos = $this->listVideos();
    usort($videos,array("FileHandler","compareByTime"));

    if($n < 0)
      return $videos;

    $first_videos = [];
    foreach($videos as $v){
      array_push($first_videos,$v);
      if (count($first_videos) >= $n)  return $first_videos;  
    }

    return $first_videos;
  }

	public function listMusics()
	{
		$musics = [];

		if(!$this->outuput_folder_exists())
			return;

		$folder = dirname(__DIR__).'/'.$this->config["outputFolder"].'/';

		foreach(glob($folder.'*'.$this->musics_ext, GLOB_BRACE) as $file)
		{
			$music = [];
			$music["name"] = str_replace($folder, "", $file);
			$music["size"] = $this->to_human_filesize(filesize($file));
			
			$musics[] = $music;
		}

		return $musics;
	}

	public function delete($id, $type)
	{
		$folder = dirname(__DIR__).'/'.$this->config["outputFolder"].'/';
		$i = 0;

		if($type === 'v')
		{
			$exts = $this->videos_ext;
		}
		elseif($type === 'm')
		{
			$exts = $this->musics_ext;
		}
		else
		{
			return;
		}

		foreach(glob($folder.'*'.$exts, GLOB_BRACE) as $file)
		{
			if($i == $id)
			{
				unlink($file);
			}
			$i++;
		}
	}

	private function outuput_folder_exists()
	{
		if(!is_dir($this->config['outputFolder']))
		{
			//Folder doesn't exist
			if(!mkdir('./'.$this->config['outputFolder'], 0777))
			{
				return false; //No folder and creation failed
			}
		}
		
		return true;
	}

	public function to_human_filesize($bytes, $decimals = 0)
	{
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	public function free_space()
	{
		return $this->to_human_filesize(disk_free_space($this->config["outputFolder"]));
	}

	public function get_downloads_folder()
	{
		return $this->config["outputFolder"];
	}
}

?>
