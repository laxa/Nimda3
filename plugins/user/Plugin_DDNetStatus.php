<?php

class Plugin_DDNetStatus extends Plugin
{
	public $triggers = array('!ddnetstatus');
	public $interval = 300;
	public $enabledByDefault = false;
	public $hideFromHelp = true;
	private $status = array();

	function onLoad()
	{
	  $page = libHTTP::GET('http://ddnet.tw/status/json/stats.json');
	  if ($page == false || strlen($page) === 0) return;
	  $this->status = json_decode($page, true);
	}

	function onInterval()
	{
	  if (!isset($this->status))
	    {
	      $page = libHTTP::GET('http://ddnet.tw/status/json/stats.json');
	      if ($page == false || strlen($page) === 0) return;
	      $this->status = json_decode($page, true);
	      return;
	    }
	  $page = libHTTP::GET('http://ddnet.tw/status/json/stats.json');
	  if ($page == false || strlen($page) === 0) return;
	  $status = json_decode($page, true);
	  foreach ($status["servers"] as $new) // check for status change
	    {
	      foreach ($this->status["servers"] as $previous)
		{
		  if ($previous["name"] === $new["name"] && $previous["online4"] != $new["online4"])
		    {
		      $updown = $new["online4"] === true ? "went back online!" : "went down!";
		      $msg = "[DDNet]\x02".$new["name"]."\x02 ".$updown;
		      $this->sendToEnabledChannels($msg);
		    }
		}
	    }
	  if (count($status["servers"]) != count($this->status["servers"])) // check if new or deleted server
	    {
	      $onlineprevious = array();
	      $onlinenew = array();
	      foreach ($status["servers"] as $new)
		$onlinenew[] = $new["name"];
	      foreach ($this->status["servers"] as $previous)
		$onlineprevious[] = $previous["name"];
	      foreach ($onlinenew as $name)
		{
		  if (!in_array($name, $onlineprevious))
		    $this->sendToEnabledChannels("[DDNet]New server detected : \x02".$name."\x02!");
		}
	      foreach ($onlineprevious as $name)
		{
		  if (!in_array($name, $onlinenew))
		    $this->sendToEnabledChannels("[DDNet]Server was removed : \x02".$name."\x02!");
		}
	    }
	  $this->status = $status;
	}
}

?>
