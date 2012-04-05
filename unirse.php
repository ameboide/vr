<?php
require_once 'base.php';
sesion();

$id = $db->campo('id', 'juegos', array('id' => $_REQUEST['juego']));
if(!empty($id)){
	$estoy = $db->campo('id', 'jugadores',
		array('juego_id' => $id, 'usuario_id' => $_SESSION['usuario']['id']));

	if(!$estoy){
		$num = $db->campo('COUNT(*)', 'jugadores', array('juego_id' => $id));
		$colores = array('#a00', '#00a', '#0a0', '#aa0', '#0aa', '#a0a');
		$color = $num > 5 ? 'rgb('.rand(0, 255).','.rand(0, 255).','.rand(0, 255).')' : $colores[$num];
		$j = $db->insert('jugadores', array(
			'juego_id' => $id,
			'usuario_id' => $_SESSION['usuario']['id'],
			'color' => $color));
	}
}
redir('jugar.php?juego='.$id);
?>