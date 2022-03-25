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
        $context = stream_context_create(array('http' =>
            array('timeout' => 2.0)));
        $page = file_get_contents('http://ddnet.tw/releases/', false, $context);
        if ($page === FALSE || strlen($page) == 0) return;
        $array = array();
        $regex = '#\/ranks\/([^\/]+).+?(\d{4}-\d{2}-\d{2} \d{2}:\d{2}).+?span title[^>]+>([^<]+).+?mappers\/([^\/]+)#';
        preg_match_all($regex, $page, $array, PREG_SET_ORDER);
        for ($i = 0; $i < sizeof($array); $i++)
            {
                if (strtotime($this->last) >= strtotime($array[$i][2])) break;
                $released = $array[$i][2];
                $difficulty = ucfirst($array[$i][1]);
                $map = html_entity_decode($array[$i][3]);
                $mapper = $array[$i][4];
                $format = "\x02$map\x02 by $mapper just released on $difficulty at $released";
                $this->sendToEnabledChannels($format);
            }
        $this->last = $array[0][2];
        if ($this->last === NULL) return;
        $this->saveVar('ddnetlatestmaprelease', $this->last);
	}
}

?>
