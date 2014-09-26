<?php

class Plugin_DDNetStatus extends Plugin
{
	public $triggers = array('!ddnetstatus');
	public $interval = 60;
	public $enabledByDefault = false;
	public $hideFromHelp = true;
	private $status = array();

	function onLoad()
	{
	  $page = libHTTP::GET('http://ddnet.tw/status/json/stats.json');
	  if ($page == false || strlen($page) === 0) return;
	  $this->status = $this->buildArray(json_decode($page, true));
	}

	private function buildArray($json)
	{
	  $array = array();
	  foreach ($json['servers'] as $server)
	    $array[$server['name']] = $server['online4'];
	  return $array;
	}

	function onInterval()
	{
	  if (sizeof($this->status) === 0)
	    {
	      $page = libHTTP::GET('http://ddnet.tw/status/json/stats.json');
	      if ($page == false || strlen($page) === 0) return;
	      $this->status = $this->buildArray(json_decode($page, true));
	      return;
	    }
	  $page = libHTTP::GET('http://ddnet.tw/status/json/stats.json');
	  if ($page == false || strlen($page) === 0) return;
	  $status = $this->buildArray(json_decode($page, true));
	  foreach ($status as $key => $online)
	    {
	      if (isset($this->status[$key]) && $this->status[$key] != $online)
		{
		  $updown = $online === true ? 'went back online!' : 'went down!';
		  $msg = "[DDNet]\x02$key\x02 ".$updown;
		  $this->sendToEnabledChannels($msg);
		}
	    }
	  if (sizeof($status) != sizeof($this->status))
	    {
	      foreach ($status as $key => $value)
		{
		  if (!isset($this->status[$key]))
		    $this->sendToEnabledChannels("[DDNet]New server detected : \x02$key\x02!");
		}
	      foreach ($this->status as $key => $value)
		{
		  if (!isset($status[$key]))
		    $this->sendToEnabledChannels("[DDNet]Server was removed : \x02$key\x02!");
		}
	    }
	  $this->status = $status;
	}
}

?>
