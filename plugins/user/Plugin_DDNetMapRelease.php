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
	  for ($i = 0; $i < sizeof($array[1]); $i++)
	    {
	      if (strtotime($this->last) >= strtotime($array[1][$i])) break;
	      $released = $array[1][$i];
	      $difficulty = ucfirst($array[3][$i]);
	      $map = html_entity_decode($array[4][$i]);
	      $mapperArray = array();
	      preg_match_all('#mappers\/[^>]+>([^<]+)#', $array[5][$i], $mapperArray);
	      $mapper = '';
	      for ($ii = 0; $ii < sizeof($mapperArray[1]); $ii++)
		{
		  $tmp = html_entity_decode($mapperArray[1][$ii]);
		  $mapper .= "\x02$tmp\x02";
		  if ($ii + 1 != sizeof($mapperArray[1]))
		    $mapper .= ' & ';
		}
	      if (strlen($mapper === 0))
		$format = "\x02$map\x02 just released on $difficulty at $released";
	      else
		$format = "\x02$map\x02 by $mapper just released on $difficulty at $released";
	      $this->sendToEnabledChannels($format);
	    }
	  $this->last = $array[1][0];
	  $this->saveVar('ddnetlatestmaprelease', $this->last);
	}
}

?>
