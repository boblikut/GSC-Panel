<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Panel implements MessageComponentInterface {
    protected $clients;
	protected $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
		$this->db = new \SQLite3("/var/www/html/db/gsc-panel.db");
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
		$arr = json_decode($msg, true);
		if ($arr['id'] == 'gmod'){
			$statement = $this->db->prepare('SELECT * FROM gmod_token WHERE token=?');
			$statement->bindValue(1, $arr['token'], SQLITE3_TEXT);
			$result = $statement->execute();
			if ($result->fetchArray() == null) {
				return;
			}
			goto msg; //skip laws checking becouse gmod can send any messages. The main is don't lick gmod token :)
		} else {
			$statement = $this->db->prepare('SELECT * FROM users WHERE id=? AND ws_token=?');
			$statement->bindValue(1, $arr['id'], SQLITE3_INTEGER);
			$statement->bindValue(2, $arr['token'], SQLITE3_TEXT);
			$result = $statement->execute();
		}
		$user = $result->fetchArray();
		if ($user == null) {
			echo 'Account fail';
			return;
		}
		$result = $this->db->query('SELECT * FROM roles WHERE name=\''.$user['role'].'\'');
		$role = $result->fetchArray(SQLITE3_ASSOC);
		$permissions = explode('|', $role['permissions']);
		if ($arr['msg']['type'] == 'maps_list_request' && !in_array('map', $permissions)){
			echo 'Map fail';
			return;
		}
		if ($arr['msg']['type'] == 'gamemodes_list_request' && !in_array('gamemode', $permissions)){
			echo 'GM fail';
			return;
		}
		if ($arr['msg']['type'] == 'game_activity' && !in_array($arr['msg']['data']['command'], $permissions)){
			echo 'Kick fail';
			return;
		}
		//sending msg
		msg:
		$msg = json_encode($arr['msg']);
		foreach ($this->clients as $client) {
			if ($from !== $client) {
				$client->send($msg);
			}
		}
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}
?>
