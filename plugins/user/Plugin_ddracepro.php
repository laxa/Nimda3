<?php

class Plugin_DDracepro extends Plugin {
  
  public $interval = 60;
  public $triggers = array('!ddracepro');
  public $enabledByDefault = false;
  
  public $helpText = 'Checks periodically if new records on DDracepro\' servers are made and prints them in the channel.';
  
  function onLoad() {
    $latest = $this->getVar('latest');
    if(!$latest) $this->saveVar('latest', date('Y-m-d H:i:s'));
  }
  
  function onInterval() {
    $channels = $this->getEnabledChannels();
    if(empty($channels)) return;
    
    $this->getRecords();
  }
  
  private function getRecords() {
    $sql = "SELECT * FROM db_teeworlds.records WHERE created > '".$this->getVar('latest')."' ORDER BY created ASC";
    $records = $this->MySQL->query($sql);
    if(empty($records)) return;
    
    foreach($records as $record) {
      $data = $this->getRecordData($record);
      
      $rankdata = $this->getRankData($record);
      if($rankdata === false) continue;
      
      $text = sprintf("\x02[DDracepro]\x02 %s just made rank %d on %s with a time of %s and %.2f seconds.",
		      $data['player'],
		      $rankdata['rank'],
		      str_replace('_', ' ', $data['map']),
		      libString::plural('minute', $data['minutes']),
		      $data['seconds']
		      );
      
      if($rankdata['rank'] != 1) {
	$text.= sprintf(" Rank 1 is held by %s with a time of %s and %.2f seconds.",
			$rankdata['rank1']['player'],
			libString::plural('minute', $rankdata['rank1']['minutes']),
			$rankdata['rank1']['seconds']
			);
      }
      
      $this->sendToEnabledChannels($text);
    }
    
    $last_record = array_pop($records);
    $this->saveVar('latest', $last_record['created']);
    
  }
  
  private function getRankData($record) {
    $sql = "
SELECT
`player`,
MIN(`time`) AS `time`,
`map`
FROM
db_teeworlds.records
WHERE
map = '".addslashes($record['map'])."'
GROUP BY
`player`
ORDER BY
`time` ASC
";
    $res = $this->MySQL->query($sql);
    
    $check = false;
    for($rank=1;$rank<=sizeof($res);$rank++) {
      if($record['player'] == $res[$rank-1]['player'] && $record['time'] == $res[$rank-1]['time']) {
	$check = true;
	break;
      }
    }
    
    if(!$check) return false;
    
    $data['rank']     = $rank;
    $data['rank1']    = $this->getRecordData($res[0]);
    
    return $data;
  }
  
  private function getRecordData($record) {
    $data = array();
  
    $player = $record['player'];
    $player = libIRC::noHighlight($player);
  
    $data['player'] = $player;
    $data['minutes'] = floor($record['time']/60);
    $data['seconds'] = $record['time']-$data['minutes']*60;
    $data['map']     = $record['map'];
    
    return $data;
  }
  
}

?>
