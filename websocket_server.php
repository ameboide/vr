<?php
// Run from command prompt > php asdasd.php

require_once("db.php");
chdir('phpws');
require_once("websocket.server.php");

/**
clase q maneja los mensajes enviados a /vr/
repite todo lo q le llega a todos los usuarios conectados al mismo juego
ademas guarda en bd las acciones
*/
class VRHandler extends WebSocketUriHandler{

	public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg){
		try{
			//safari termina sus mensajes con un 0xFF...
			$data_txt = $msg->getData();
			$data_txt = preg_replace('/^[^{]*(\{.+\})[^}]+$/', '$1', $data_txt);
			if(empty($data_txt)) return;

			$data = json_decode($data_txt);
			if(isset($data->apagar)) exit;

			$amigos = $this->amigos($user);
			if(empty($amigos)) return;

			foreach($amigos as $u) $u->sendMessage($msg);

			$id = $this->param($user, 'id');

			$db = new DB();

			switch($data->accion){
				case 'mover':
					$update = array();
					if(isset($data->vueltas)) $update['vueltas'] = $data->vueltas;
					if(isset($data->lugar)) $update['lugar'] = $data->lugar;
					if(isset($data->choque)){
						$update['choque_x'] = $data->choque ? $data->choque->x : null;
						$update['choque_y'] = $data->choque ? $data->choque->y : null;
					}

					if(!empty($update)) $db->update('jugadores', $update, array('id' => $id));

				case 'posicion':
					$db->insert('movimientos', array(
						'jugador_id' => $id,
						'pos_x' => $data->pos->x,
						'pos_y' => $data->pos->y));
					break;

				case 'join':
					$online = array();
					foreach($amigos as $u){
						if($this->param($u, 'id')) $online[] = $this->param($u, 'id');
					}

					$msg->setData(json_encode(array(
						'accion' => 'lista_online',
						'ids' => $online)));
					$user->sendMessage($msg);
					break;

				case 'chat':
					$db->insert('chats', array(
						'jugador_id' => $id,
						'juego_id' => $this->param($user, 'juego'),
						'txt' => $data->msg
					));
			}
		}
		catch(Exception $e){
			$this->say('FAIL: '.$e->getMessage());
		}
	}

	public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $obj){
		$this->say("[VR] Admin TEST received!");

		$frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
		$user->sendFrame($frame);
	}

	function param(IWebSocketConnection $user, $par){
		if(!isset($user->parameters[$par])) return null;
		return $user->parameters[$par];
	}

	function amigos(IWebSocketConnection $user){
		$juego = $this->param($user, 'juego');
		$users = array();
		foreach($this->users as $u){
			if($this->param($u, 'juego') == $juego) $users[] = $u;
		}
		return $users;
	}

	function despedir(IWebSocketConnection $user){
		//$msg = new WebSocketMessage();
		$amigos = $this->amigos($user);
		//foreach($amigos as $u) $u->sendMessage($msg);
	}
}

/**
 * VR socket server. Implements the basic eventlisteners and attaches a resource handler for /echo/ urls.
 *
 *
 * @author Chris
 *
 */
class VRSocketServer implements IWebSocketServerObserver{
	protected $debug = false;
	protected $server;
	var $handler = null;

	public function __construct(){
		$this->server = new WebSocketServer(0, 12345, 'superdupersecretkey23');
		$this->server->addObserver($this);

		$this->handler = new VRHandler();
		$this->server->addUriHandler("vr", $this->handler);
	}

	public function onConnect(IWebSocketConnection $user){
		$this->say("[VR] {$user->getId()} connected: ".print_r($user->parameters, true));
	}

	public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg){
		$this->say("[VR] {$user->getId()} says '{$msg->getData()}'");
	}

	public function onDisconnect(IWebSocketConnection $user){
		$this->say("[VR] {$user->getId()} disconnected");
		$this->handler->despedir($user);
	}

	public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg){
		$this->say("[VR] Admin Message received!");

		$frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
		$user->sendFrame($frame);
	}

	public function say($msg){
		echo "$msg \r\n";
	}

	public function run(){
		$this->server->run();
	}
}

// Start server
$server = new VRSocketServer(0,12345);
$server->run();