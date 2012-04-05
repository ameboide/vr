<?php
require_once 'db.php';
$db = new DB();

session_start();
function sesion(){
	if(empty($_SESSION['usuario'])){
		$_SESSION['redir'] = $_SERVER['REQUEST_URI'];
		redir('login.php');
	}
}

function redir($url){
	header("Location: $url");
	exit;
}

function pag_head($titulo=''){ ?>
<html>
	<head>
		<title>VxR - <?=$titulo?></title>
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/dibujo.js"></script>
		<script type="text/javascript" src="js/juego.js"></script>
		<script type="text/javascript" src="js/chat.js"></script>
		<script type="text/javascript" src="js/websocket.js"></script>
		<link rel="stylesheet" type="text/css" href="juego.css">
	</head>
	<body>
<?php if(!empty($_SESSION['usuario'])) { ?>
		<span style="float:right">holo <?=$_SESSION['usuario']['nombre']?> [<a href="logout.php">salir</a>]</span>
<?php }
}

function pag_foot(){ ?>
	</body>
</html>
<?php
}

?>