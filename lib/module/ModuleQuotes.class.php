<?php
/**
 * Posts a message when a user joins
 *
 * @author	Tim Düsterhus
 * @copyright	2010 - 2011 Tim Düsterhus
 */
class ModuleQuotes extends Module {
	protected $config = null;
	protected $coolDown = array();

	public function __construct() {
		$this->config = new Config('quotes', array());
		$this->config->write();
	}
	
	public function destruct() {
		$this->config->write();
	}
	
	public function handle(Bot $bot) {
		if ($bot->message['id'] % 500 == 0) $this->config->write();
		if ($bot->message['type'] == Bot::JOIN) {
			
			$userID = $bot->lookUpUserID();
			if (isset($this->config->config[$userID]) && (!isset($this->coolDown[$userID]) || ($this->coolDown[$userID] + 5 * 60) < time())) {
				$bot->queue('['.$bot->message['usernameraw'].'] '.substr($this->config->config[$userID], 0, 250));
				$this->coolDown[$userID] = time();
			}
		}
		else if (substr(Module::removeWhisper($bot->message['text']), 0, 7) == '!quote ') {
			$username = substr(Module::removeWhisper($bot->message['text']), 7);
			$userID = $bot->lookUpUserID($username);
			if ($userID) {
				if (isset($this->config->config[$userID])) {
					$bot->queue('/whisper '.$bot->message['usernameraw'].', ['.$username.'] '.$this->config->config[$userID]);
				}
				else {
					$bot->queue('/whisper '.$bot->message['usernameraw'].', '.Core::language()->quotes_noquote);
				}
			}
			else {
				$bot->queue('/whisper '.$bot->message['usernameraw'].', '.Core::language()->get('user_not_found', array('{user}' => $username)));
			}
		}
		else if (substr(Module::removeWhisper($bot->message['text']), 0, 10) == '!setquote ') {
			$this->config->config[$bot->lookUpUserID()] = substr(Module::removeWhisper($bot->message['text']), 10);
			$bot->success();
		}
		else if (substr(Module::removeWhisper($bot->message['text']), 0, 12) == '!forcequote ') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'quote.force')) return $bot->denied();
			$data = explode(' ', substr(Module::removeWhisper($bot->message['text']), 12), 2);
			if (count($data) != 2) return;
			list($username, $text) = $data;
			$userID = $bot->lookUpUserID($username);
                        if ($userID) {
				$this->config->config[$userID] = $text;
				$bot->success();
			}
                        else {
                                $bot->queue('/whisper '.$bot->message['usernameraw'].', '.Core::language()->get('user_not_found', array('{user}' => $username)));
                        }

		}
		else if (Module::removeWhisper($bot->message['text']) == '!delquote') {
			unset($this->config->config[$bot->lookUpUserID()]);
			$bot->success();
		}
		else if (substr(Module::removeWhisper($bot->message['text']), 0, 11) == '!wipequote ') {
			if (Core::compareLevel($bot->lookUpUserID(), 'quote.wipe')) {
				$username = substr(Module::removeWhisper($bot->message['text']), 11);
				$userID = $bot->lookUpUserID($username);
				if ($userID > 0) {
					unset($this->config->config[$userID]);
					$bot->success();
				}
				else {
					$bot->queue('/whisper '.$bot->message['usernameraw'].', '.Core::language()->get('user_not_found', array('{user}' => $username)));
				}
			}
			else {
				$bot->denied();
			}
		}
	}
}

