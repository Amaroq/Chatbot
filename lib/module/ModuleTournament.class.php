<?php
class ModuleTournament extends Module {
	protected $joinActive = false;
	protected $joined = array();
	protected $joinStart = 0;
	protected $joinRoom = 0;
	protected $teamSize = 2;
	protected $maxUsers = 4;

	public function destruct() {

	}
	
	protected function reset() {
		$this->joinStart = 0;
		$this->joinActive = false;
		$this->joined = array();
	}
	public function handle(Bot $bot) {
		if ($this->joinActive && (count($this->joined) >= $this->maxUsers || substr($bot->message['text'], 0, 8) == '!endjoin')) {
			if (substr($bot->message['text'], 0, 8) == '!endjoin' && !Core::compareLevel($bot->lookUpUserID(), 'tournament.start')) return $bot->denied();
			if (substr($bot->message['text'], 0, 8) == '!endjoin') {
				if ($bot->message['text'] != '!endjoin force') {
					if (intval(log(count($this->joined)) / log($this->teamSize)) != log(count($this->joined)) / log($this->teamSize)) {
						$bot->queue('Es sind '.count($this->joined).' Spieler beigetreten. Es müssten aber '.pow($this->teamSize, ceil(log(count($this->joined)) / log($this->teamSize))).' Spieler für gleich große Paarungen sein.');
						$bot->queue('Benutze !endjoin force um dennoch fortzufahren');
						return;
					}
				}
			}
			$this->end($bot);
		}
		if (substr($bot->message['text'], 0, 12) == '!tournament ' && !$this->joinActive) {
			if (!Core::compareLevel($bot->lookUpUserID(), 'tournament.start')) return $bot->denied();
			$params = explode(' ', substr($bot->message['text'], 12));
			if (!isset($params[0])) return;
			if (!isset($params[1])) $params[1] = 9001;
			$this->teamSize = $params[0];
			$this->maxUsers = $params[1];
			
			$bot->queue('Ein Turnier wurde gestartet. Tippe !join um beizutreten');
			$this->joinActive = true;
			$this->joinStart = time();
			$this->joinRoom = $bot->message['roomID'];
		}
		else if (substr($bot->message['text'], 0, 12) == '!tournament ') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'tournament.start')) return $bot->denied();
			$bot->queue('/whisper '.$bot->message['usernameraw'].', Es läuft gerade eine Turnier');
		}
		else if ($this->joinActive && $bot->message['text'] == '!join' && !isset($this->joined[$bot->lookUpUserID()])) {
			if (count($this->joined) >= $this->maxUsers) return $bot->queue('/whisper '.$bot->message['usernameraw'].', Das Turnier ist voll');
			
			$this->joined[$bot->lookUpUserID()] = $bot->message['usernameraw'];
			$bot->success();
		}
	}
	
	protected function end(Bot $bot) {
		$bot->queue('Die Anmeldephase ist beendet.', $this->joinRoom);
		shuffle($this->joined);
		$memberID = 1;
		$teamID = 1;
		$teamString = '';
		foreach($this->joined as $userID => $username) {
			if ($teamString != '') $teamString .= ', ';
			$teamString .= $username;
			
			if ($memberID++ == $this->teamSize) {
				$bot->queue('Paarung '.$teamID++.': '.$teamString, $this->joinRoom);
				$memberID = 1;
				$teamString = '';
			}
		}

		if ($memberID != 1) {
                	$bot->queue('Paarung '.$teamID++.': '.$teamString, $this->joinRoom);
                        $memberID = 1;
                        $teamString = '';
                }
		$this->reset();
	}
}

