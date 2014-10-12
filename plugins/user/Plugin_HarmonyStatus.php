<?php

class Plugin_HarmonyStatus extends Plugin
{
	public $triggers = array('!harmonystatus');
	public $interval = 60;
	public $enabledByDefault = false;
	public $hideFromHelp = true;
	private $status = array();

	function onLoad()
	{
	  $array = $this->getStatus();
	  if (sizeof($array) === 0)
	    return;
	  $this->status = $array;
	}

	private function buildArray($array)
	{
	  $ret = array();
	  for ($i = 0; $i < sizeof($array[1]); $i++)
	    {
	      if (array_key_exists($array[1][$i], $ret)) continue;
	      $ret[$array[1][$i]] = $array[2][$i];
	    }
	  return $ret;
	}

	private function getStatus()
	{
	  $array = array();
	  $page = libHTTP::GET('https://www.harmony-hosting.com/help/monitoring/');
	  preg_match_all('#<td>(\w+)<\/td>[^"]+"[^"]+">([^<]+)#', $page, $array);
	  $array = $this->buildArray($array);
	  return $array;
	}

	function onInterval()
	{
	  if (sizeof($this->status) === 0)
	    {
	      $array = $this->getStatus();
	      if (sizeof($array) === 0) return;
	      $this->status = $array;
	      return;
	    }
	  $status = $this->getStatus();
	  if (sizeof($status) === 0) return;
	  foreach ($status as $key => $online)
	    {
	      if (isset($this->status[$key]) && $this->status[$key] != $online)
		{
		  $updown = $online === 'En ligne' ? 'est de retour en ligne !' : 'viens de passer hors ligne !';
		  $this->sendToEnabledChannels("\x02$key\x02 $updown");
		}
	    }
	  if (sizeof($status) != sizeof($this->status))
	    {
	      foreach ($status as $key => $value)
		{
		  if (!isset($this->status[$key]))
		    $this->sendToEnabledChannels("Nouveau serveur détecté : \x02$key\x02 !");
		}
	      foreach ($this->status as $key => $value)
		{
		  if (!isset($status[$key]))
		    $this->sendToEnabledChannels("Le serveur a été enlevé : \x02$key\x02 !");
		}
	    }
	  $this->status = $status;
	}
}

?>
