<?php

class Plugin_DDNetMapRelease extends Plugin
{
	public $triggers = array('!ddnetmaprelease');
	public $interval = 300;
	public $hideFromHelp = true;
	public $enabledByDefault = false;
	private $last;

	function onLoad()
	{
	  $this->last = $this->getVar('ddnetlatestmaprelease');
	  if(!$this->last)
	    $this->last = date('Y-m-d H:i');
	}

	function onInterval()
	{
	  $page = libHTTP::GET('http://ddnet.tw/releases/');
	  if ($page === FALSE || strlen($page) == 0) return;
	  $array = array();
	  $regex = '#(\d{4}-\d{2}-\d{2} \d{2}:\d{2}).+href="(\/ranks\/(moderate|novice|solo|brutal|oldschool)\/\#map-[^"]+)"><span title[^>]+>([^<]+)(.+mappers[^>]+>([^<]+))?#';
	  preg_match_all($regex, $page, $array);
	  for ($i = 0; $i < count($array[1]); $i++)
	    {
	      if (strtotime($this->last) >= strtotime($array[1][$i])) break;
	      $released = $array[1][$i];
	      $difficulty = $array[3][$i];
	      $map = html_entity_decode($array[4][$i]);
	      if (strlen($array[6][$i]) === 0)
		$mapper = "Unknown";
	      else
		$mapper = html_entity_decode($array[6][$i]);
	      $format = "\x02$map\x02 by \x02$mapper\x02 just released on $difficulty at $released";
	      $this->sendToEnabledChannels($format);
	    }
	  $this->last = $array[1][0];
	  $this->saveVar('ddnetlatestmaprelease', $this->last);
	}
}

?>
