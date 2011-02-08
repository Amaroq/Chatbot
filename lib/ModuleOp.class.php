<?php
class ModuleOp extends Module {
	public function destruct() {

	}
	public function handle(Bot $bot) {
		if (Module::removeWhisper($bot->message['text']) == '!shutdown') {
			if (!Core::isOp($bot->lookUpUserID())) return $bot->denied();
			exit;
		}
		else if (substr(Module::removeWhisper($bot->message['text']), 0, 5) == '!say ') {
			if (!Core::isOp($bot->lookUpUserID())) return $bot->denied();
			$bot->queue(substr(Module::removeWhisper($bot->message['text']), 5));
		}
		else if (Module::removeWhisper($bot->message['text']) == '!loaded') {
			if (!Core::isOp($bot->lookUpUserID())) return $bot->denied();
			$bot->queue('/whisper "'.$bot->message['usernameraw'].'" Folgende Module sind geladen: '.implode(', ', array_keys(Core::getModules())));
		}
		else if (substr(Module::removeWhisper($bot->message['text']), 0, 6) == '!join ') {
			if (!Core::isOp($bot->lookUpUserID())) return $bot->denied();
			$bot->getConnection()->join(substr(Module::removeWhisper($bot->message['text']), 6));
			$bot->success();
		}
	}
}
