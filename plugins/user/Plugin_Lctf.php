<?php

class Plugin_Lctf extends Plugin
{
    public $triggers = array('!lctf');
    public $helpText = 'Display our stats or team\'s rank if given';
    public $interval = 1800;
    public $enabledByDefault = false;
    private $json;
    private $teamToWatch = array('0x90r00t', 'khack40', 'pony7', 'LSE');
    private $pos = array();

    public function isTriggered()
    {
        $this->getScoreboard();
        if (isset($this->data['text']))
        {
            $team = $this->data['text'];
            $data = $this->getRank($team);
            if ($data == false)
            {
                $output = sprintf("\x0303$team\x03 not found");
            }
            else
            {
                $output = sprintf("\x0303$team\x03 is \x0304%s\x03 with \x0310%d\x03 points", $data['rank'], $data['score']);
            }
            $this->reply($output);
        }
        else
        {
            foreach ($this->teamToWatch as $team)
            {
                $data = $this->getRank($team);
                $output = sprintf("\x0303$team\x03 is now \x0304%s\x03 with \x0310%d\x03 points", $data['rank'], $data['score']);
                $this->reply($output);
                if ($data['score'] !== $this->pos[$team]['score']
                    || $data['rank'] !== $this->pos[$team]['rank'])
                {
                    $this->pos[$team] = array('rank' => $data['rank'], 'score' => $data['score']);
                }
            }
        }
    }

    public function onInterval()
    {
        $this->getScoreboard();
        // if first time we get our data
        if (count($this->pos) == 0)
        {
            foreach ($this->teamToWatch as $team)
            {
                $data = $this->getRank($team);
                if ($data == false) continue;
                $this->pos[$team] = array('rank' => $data['rank'], 'score' => $data['score']);
            }
        }
        else
        {
            foreach ($this->teamToWatch as $team)
            {
                $data = $this->getRank($team);
                if ($data['score'] !== $this->pos[$team]['score']
                    || $data['rank'] !== $this->pos[$team]['rank'])
                {
                    $output = sprintf("\x0303$team\x03 is now \x0304%s\x03 with \x0310%d\x03 points", $data['rank'], $data['score']);
                    $this->sendToEnabledChannels($output);
                    $this->pos[$team] = array('rank' => $data['rank'], 'score' => $data['score']);
                }
            }
        }
    }

    private function getScoreboard()
    {
        $data = libHTTP::GET('https://ctf.internetwache.org/scoreboard.json');
        $this->json = json_decode($data, true);
        $this->json = $this->json['standings'];
        // sorting array
        usort($this->json, array('Plugin_Lctf', 'sortTeams'));
    }

    private static function sortTeams($a, $b)
    {
        return $a['score'] > $b['score'] ? -1 : 1;
    }

    private function getRank($team)
    {
        $size = count($this->json);
        for ($i = 0; $i < $size && $this->json[$i]['score'] > 0; $i++)
        {
            if ($this->json[$i]['team'] == $team)
            {
                return array('score' => $this->json[$i]['score'], 'rank' => $i + 1);
            }
        }
        return false;
    }
}

?>
