<?php
require_once 'base.php';

if(!empty($_POST['nombre'])){
	$datos = array(
		'nombre' => $_POST['nombre'],
		'password' => $db->hash($_POST['password']));

	$pass = $db->campo('password', 'usuarios', array('nombre' => $datos['nombre']));
	if($pass && $pass != $datos['password']){
		echo 'fail';
	}
	else{
		if(!$pass){
			$db->insert('usuarios', $datos);
		}

		$_SESSION['usuario'] = $db->select('*', 'usuarios', array('nombre' => $datos['nombre']));
		$_SESSION['usuario'] = $_SESSION['usuario'][0];
	}
}

if(!empty($_SESSION['usuario'])){
	$url = $_SESSION['redir'];
	unset($_SESSION['redir']);
	redir($url ? $url : '.');
}

pag_head('login');
?>
<form action="login.php" method="POST">
	nombre: <input type="text" name="nombre"/>
	password: <input type="password" name="password"/>
	<button type="submit">entrar</button>
</form>
(si no existe el usuario, se crea con esos datos)
<?php pag_foot();?>