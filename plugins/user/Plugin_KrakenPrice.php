<?php

class Plugin_KrakenPrice extends Plugin
{
	public $triggers = array('!kraken', '!kra');
	public $interval = 120;
    public $usage = '';
	public $helpText = 'Prints kraken price.';

    private $whitelist_cur = array('xbt', 'eth', 'bch', 'etc', 'ltc', 'rep', 'xmr', 'xrp', 'zec', 'xrp');
    private $whitelist_real = array('eur', 'usd');

    function onLoad()
    {
        foreach ($this->whitelist_cur as $cur)
            {
                $this->usage .= $cur . ',';
            }
        $this->usage = rtrim($this->usage, ',');
        $this->usage .= '<';
        foreach ($this->whitelist_real as $real)
            {
                $this->usage .= $real . '|';
            }
        $this->usage = rtrim($this->usage, '|');
        $this->usage .= '>';
    }

	function isTriggered()
	{
        $request_cur = '';
        $request_data = strtolower($this->data['text']);
        /* if no option, default currency is eth */
        if (strlen($request_data) === 0) $request_data = 'eth';
        foreach (explode(' ', $request_data) as $request)
            {
                /* default real currency is eur */
                $request_real = 'eur';
                $split = str_split($request, 3);
                if ($split != FALSE && in_array($split[0], $this->whitelist_cur))
                    {
                        $request_cur = $split[0];
                        if (isset($split[1]) && strlen($split[1]) > 0 &&
                        in_array($split[1], $this->whitelist_real))
                            $request_real = $split[1];
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
                if (is_array($data) == false || !isset($data['result'][$place_holder]['c'][0]))
                    {
                        $this->reply("Error while parsing data");
                        return;
                    }
                $value = (float)$data['result'][$place_holder]['c'][0];
                $digit = 2;
                if ($value < (float)1) $digit = 4;
                $this->reply(sprintf("%s: %.${digit}f%s", strtoupper($request_cur),
                $value,
                $request_real === 'usd' ? '$' : '€'));
            }
    }
}

?>
