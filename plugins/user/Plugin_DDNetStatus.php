<?php

class Plugin_DDNetStatus extends Plugin
{
	public $triggers = array('!ddnetstatus');
	public $interval = 60;
	public $enabledByDefault = false;
	public $hideFromHelp = true;
	private $status = array();
	private $mainserverdown = false;

	function onLoad()
	{
	  $page = $this->getStatus();
	  if ($page === false || strlen($page) === 0)
	    {
	      $this->mainserverdown = true;
	      return;
	    }
	  $this->status = $this->buildArray(json_decode($page, true));
	}

	private function buildArray($json)
	{
	  $array = array();
	  foreach ($json['servers'] as $server)
	    $array[$server['name']] = $server['online4'];
	  return $array;
	}

	private function getStatus()
	{
	  return libHTTP::GET('http://ddnet.tw/status/json/stats.json');
	}

	function onInterval()
	{
	  if (sizeof($this->status) === 0)
	    {
	      $page = $this->getStatus();
	      if ($page === false || strlen($page) === 0) return;
	      $this->sendToEnabledChannels("\x02DDNet FRA\x02 went back online!");
	      $this->mainserverdown = false;
	      $this->status = $this->buildArray(json_decode($page, true));
	      return;
	    }
	  $page = $this->getStatus();
	  if ($page === false || strlen($page) === 0)
	    {
	      if (!$this->mainserverdown)
		{
		  $this->sendToEnabledChannels("\x02DDNet FRA\x02 went down!");
		  $this->mainserverdown = true;
		}
	      return;
	    }
	  if ($this->mainserverdown)
	    {	      
	      $this->sendToEnabledChannels("\x02DDNet FRA\x02 went back online!");
	      $this->mainserverdown = false;
	    }
	  $status = $this->buildArray(json_decode($page, true));
	  foreach ($status as $key => $online)
	    {
	      if (isset($this->status[$key]) && $this->status[$key] != $online)
		{
		  $updown = $online === true ? 'went back online!' : 'went down!';
		  $msg = "\x02$key\x02 ".$updown;
		  $this->sendToEnabledChannels($msg);
		}
	    }
	  if (sizeof($status) != sizeof($this->status))
	    {
	      foreach ($status as $key => $value)
		{
		  if (!isset($this->status[$key]))
		    $this->sendToEnabledChannels("New server detected : \x02$key\x02!");
		}
	      foreach ($this->status as $key => $value)
		{
		  if (!isset($status[$key]))
		    $this->sendToEnabledChannels("Server was removed : \x02$key\x02!");
		}
	    }
	  $this->status = $status;
	}
}

?>
