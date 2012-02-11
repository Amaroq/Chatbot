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
		if ($this->joinActive && (count($this->joined) >= $this->maxUsers || $bot->message['text'] == '!endjoin')) {
			if (count($this->joined) < $this->maxUsers && !Core::compareLevel($bot->lookUpUserID(), 'tournament.start')) return $bot->denied();
			$this->end($bot);
		}
		if (substr($bot->message['text'], 0, 12) == '!tournament ' && !$this->joinActive) {
			if (!Core::compareLevel($bot->lookUpUserID(), 'tournament.start')) return $bot->denied();
			$params = explode(' ', substr($bot->message['text'], 12));
			if (!isset($params[0])) return;
			if (!isset($params[1])) return;
			$this->teamSize = $params[0];
			$this->maxUsers = $params[1];
			
			$bot->queue('Ein Turnier mit '.$params[1].' Spielern und einer Teamgröße von '.$params[0].' wurde gestartet. Tippe !join um beizutreten');
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
		$bot->queue('Die Beitrittphase ist beendet.', $this->joinRoom);
		shuffle($this->joined);
		$memberID = 1;
		$teamID = 1;
		$teamString = '';
		foreach($this->joined as $userID => $username) {
			if ($teamString != '') $teamString .= ', ';
			$teamString .= $username;
			
			if ($memberID++ == $this->teamSize) {
				$bot->queue('Team '.$teamID++.': '.$teamString, $this->joinRoom);
				$memberID = 1;
				$teamString = '';
			}
		}
		$this->reset();
	}
}

