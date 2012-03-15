<?php
/**
 * Posts a message when a user joins
 *
 * @author	Tim Düsterhus
 * @copyright	2010 - 2011 Tim Düsterhus
 */
class ModuleNotice extends Module {
	protected $config = null;

	public function __construct() {
		$this->config = new Config('notice', array('users' => array(), 'message' => ''));
		$this->config->write();
	}
	
	public function destruct() {
		$this->config->write();
	}
	
	public function handle(Bot $bot) {
		if ($bot->message['id'] % 500 == 0) $this->config->write();
		if ($bot->message['type'] == Bot::JOIN && $this->config->config['message'] !== '') {
			$userID = $bot->lookUpUserID();
			if (!isset($this->config->config['users'][$userID])) {
				$bot->queue('/whisper '.$bot->message['usernameraw'].', '.$this->config->config['message']);
				$this->config->config['users'][$userID] = true;
			}
		}
		else if (substr(Module::removeWhisper($bot->message['text']), 0, 11) == '!setnotice ') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'notice.set')) return $bot->denied();
			$notice = substr(Module::removeWhisper($bot->message['text']), 11);
			$this->config->config['message'] = $notice;
			$bot->success();
		}
		else if (Module::removeWhisper($bot->message['text']) == '!wipenotice') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'notice.set')) return $bot->denied();
			$this->config->config['message'] = '';
			$bot->success();
		}
		else if (Module::removeWhisper($bot->message['text']) == '!wipenoticestats') {
			if (!Core::compareLevel($bot->lookUpUserID(), 'notice.wipe')) return $bot->denied();
			$this->config->config['users'] = array();
			$bot->success();
		}
	}
}

