<?php
define('USERAGENT', 'PHP/'.phpversion().' ('.php_uname('s').' '.php_uname('r').') Chatbot/2.0');
if (!defined('API_KEY')) define('API_KEY', '');
require_once(DIR.'lib/3rdParty/wcfapi/WBBApi.class.php');

/**
 * WCFApi provides methods to externally access a WCF
 * 
 * @author	Tim Düsterhus
 * @copyright	2010 - 2011 Tim Düsterhus
 * @licence	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class Connection extends WBBApi {
	/**
	 * Joins the chat
	 *
	 * @return	string		request answer
	 */
	public function joinChat() {
		$this->url['query'] = 'page=Chat';
		return $this->setRequest();
	}
	
	/**
	 * Sends a message
	 *
	 * @param	string		$message	message to send
	 * @return	void
	 */
	public function postMessage($message, $enableHTML = false) {
		if (strpos($message, ' ') === false) return;
		list($roomID, $message) = explode(' ', $message, 2);
		$this->url['query'] = 'form=Chat'.(API_KEY != '' ? 'Bot' : ''); 
		$this->request = array(
			'text' => $message,
			'enablesmilies' => 1,
			'ajax' => 1,
			'room' => $roomID,
			'key' => API_KEY
		);
		if ($enableHTML) $this->request['enableHTML'] = true;

		$this->setRequest(false);
	}
	
	/**
	 * Reads the message after $id
	 *
	 * @param	integer		$id	offset
	 * @return	array<array>		json result
	 */
	public function readMessages($id) {
		$this->url['query'] = 'page=ChatMessage'.(API_KEY != '' ? 'Bot' : '').'&id='.$id;
		$this->request = array(
			'key' => API_KEY
		);
		$data = $this->setRequest();
		$data = substr($data, stripos($data, '{"users'));
		$data = substr($data, 0, strrpos($data, '}}')+2);
		$data = json_decode($data, true);
		return $data;
	}
	
	/**
	 * Joins the room
	 * 
	 * @param	integer		$roomID		room to join
	 * @return	string				request answer
	 */
	public function join($roomID) {
		$this->url['query'] = 'page=Chat&ajax=1&room='.$roomID;
		$data = $this->setRequest(false);
		return $data;
	}
	
	/**
	 * Returns the roomlist
	 *
	 * @return	string		request answer
	 */
	public function getRooms() {
		$this->url['query'] = 'page=ChatRefreshRoomList';
		$data = $this->setRequest();
		preg_match_all('~<a class="room([0-9]+)-([^"]+)">~Ui', $data, $matches);
		
		$rooms = array();
		for ($i = 0, $max = count($matches[0]); $i < $max; $i++) {
			$rooms[$matches[1][$i]] = $matches[2][$i];
		}
		return $rooms;
	}

	public function getCurrentRoom() {
		$this->url['query'] = 'page=ChatRefreshRoomList';
                $data = $this->setRequest();
		preg_match('~<li class="active" id="room([0-9]+)Item">~', $data, $match);
		
		return $match[1];
	}

	/**
	 * Leaves the chat
	 *
	 * @return	void
	 */
	public function leave() {
		$this->url['query'] = 'form=Chat&kill=1';
		$this->setRequest(false);
	}
}

