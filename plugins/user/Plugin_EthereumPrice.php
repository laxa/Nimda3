<?php

class Plugin_EthereumPrice extends Plugin
{
	public $triggers = array('!eth');
	public $interval = 120;
	public $helpText = 'Prints the current ethereum price.';

	function isTriggered()
	{
        $data = file_get_contents('https://api.kraken.com/0/public/Ticker?pair=ETHEUR');
        if ($data === false)
            {
                $this->reply("Error while retrieving ETH data");
                return;
            }
        $data = json_decode($data, true);
        if (is_array($data) == false || !isset($data['result']['XETHZEUR']['a'][0]))
            {
                $this->reply("Error while parsing data");
                return;
            }
        $this->reply(sprintf("ETH price: %.2f€", $data['result']['XETHZEUR']['a'][0]));
	}
}

?>
