<?php

class Plugin_KrakenPrice extends Plugin
{
	public $triggers = array('!kraken', '!kra');
	public $interval = 120;
	public $helpText = 'Prints kraken price.';

    private $whitelist_cur = array('xbt', 'eth', 'bch', 'etc', 'ltc', 'rep', 'xmr', 'xrp', 'zec');
    private $whitelist_real = array('eur', 'usd');

	function isTriggered()
	{
        $request_cur = 'eth';
        $request_real = 'eur';
        if (isset($this->data['text']))
            {
                $request_data = strtolower($this->data['text']);
                $split = str_split($request_data, 3);
                if ($split != FALSE && in_array($split[0], $this->whitelist_cur) &&
                in_array($split[1], $this->whitelist_real))
                    {
                        $request_cur = $split[0];
                        $request_real = $split[1];
                    }
            }

        $request = $request_cur.$request_real;
        $context = stream_context_create(array('https' =>
            array('timeout' => 2.0)));
        $data = file_get_contents("https://api.kraken.com/0/public/Ticker?pair=$request",
            false, $context);
        if ($data === false)
            {
                $this->reply("Error while retrieving data");
                return;
            }
        $data = json_decode($data, true);
        $place_holder = strtoupper('X'.$request_cur.'Z'.$request_real);
        var_dump($place_holder);
        var_dump($data);
        if (is_array($data) == false || !isset($data['result'][$place_holder]['c'][0]))
            {
                $this->reply("Error while parsing data");
                return;
            }
        $this->reply(sprintf("%s: %.2f%s", strtoupper($request_cur),
            $data['result'][$place_holder]['c'][0],
            $request_real === 'usd' ? '$' : '€'));
    }
}

?>
