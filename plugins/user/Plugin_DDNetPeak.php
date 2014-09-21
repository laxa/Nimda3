<?php

class Plugin_DDNetPeak extends Plugin
{
	public $triggers = array('!ddnetpeak');
	public $interval = 120;
	public $helpText = 'Prints the current ddnet peak and when it was achieved.';
	public $helpCategory = 'Teeworlds';

	protected $config = array(
		'ddnetpeakshow' => array(
			'type' => 'enum',
			'options' => array('yes', 'no'),
			'default' => 'no',
			'description' => 'Determines wherever or not new ddnet peaks should be announced in the channel.'
		)
	);

	function isTriggered()
	{
	  $res = $this->MySQL->fetchRow("select * from db_teeworlds.online where id=1");
	  $page_status = libHTTP::GET('http://ddnet.tw/status');
	  if ($page_status !== FALSE)
	    {
	      preg_match("#DDraceNetwork Status: ([0-9]{1,10}) player#", $page_status, $array);
	      $total = $array[1];
	      $this->reply(sprintf("Current players on DDNet : %d", $total));
	    }
	  $this->reply(sprintf("Current \x02DDNet\x02 peak : %d users online at %s", $res['online'], $res['created']));
	}

	function onLoad()
	{
	  $total = $this->getVar('ddnetpeak');
	  if(!$total) $this->saveVar('ddnetpeak', $this->MySQL->fetchColumn("SELECT online FROM db_teeworlds.online WHERE id=1"));
	}

	function onInterval()
	{
	  $res = $this->MySQL->fetchRow("select * from db_teeworlds.online where id=1");
	  if ($this->getVar('ddnetpeak') < $res['online'])
	    {
	      $text = "New \x02DDNet\x02 peak : ".$res['online']." users online at ".$res['created'];
	      $this->sendToChannelsWithConfig('ddnetpeakshow', 'yes', $text);
	      $this->saveVar('ddnetpeak', $res['online']);
	    }
	}
}

?>
