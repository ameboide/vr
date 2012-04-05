#!/php -q
<?php

// Run from command prompt > php demo.php
require_once("websocket.server.php");

/**
 * This demo resource handler will respond to all messages sent to /echo/ on the socketserver below
 *
 * All this handler does is echoing the responds to the user
 * @author Chris
 *
 */
class DemoEchoHandler extends WebSocketUriHandler{
	public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg){
		$this->say("[ECHO] {$msg->getData()}");
		// Echo
		$user->sendMessage($msg);

		$caca = $this->caca($user);
		if(!$caca) return;
		$msg->setData('repitiendo '.$msg->getData());
		foreach($this->users as $u){
			if($this->caca($u) == $caca) $u->sendMessage($msg);
		}
	}

	public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $obj){
		$this->say("[DEMO] Admin TEST received!");

		$frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
		$user->sendFrame($frame);
	}

	function caca(IWebSocketConnection $user){
		if(!isset($user->parameters['caca'])) return null;
		return $user->parameters['caca'];
	}
}

/**
 * Demo socket server. Implements the basic eventlisteners and attaches a resource handler for /echo/ urls.
 *
 *
 * @author Chris
 *
 */
class DemoSocketServer implements IWebSocketServerObserver{
	protected $debug = true;
	protected $server;

	public function __construct(){
		$this->server = new WebSocketServer(0, 12345, 'superdupersecretkey');
		$this->server->addObserver($this);

		$this->server->addUriHandler("echo", new DemoEchoHandler());
	}

	public function onConnect(IWebSocketConnection $user){
		$this->say("[DEMO] {$user->getId()} connected");
	}

	public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg){
		$this->say("[DEMO] {$user->getId()} says '{$msg->getData()}'");
	}

	public function onDisconnect(IWebSocketConnection $user){
		$this->say("[DEMO] {$user->getId()} disconnected");
	}

	public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg){
		$this->say("[DEMO] Admin Message received!");

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
$server = new DemoSocketServer(0,12345);
$server->run();