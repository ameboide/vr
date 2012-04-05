<?php
require_once 'base.php';
sesion();
$juegos = $db->select('*', 'juegos');
$estados = array('P'=>'posicionandose', 'J'=>'jugando', 'F'=>'finalizado');
pag_head('lista de juegos');
?>
juegos:
<table>
	<tr>
		<th>nombre</th>
		<th>pista</th>
		<th>jugadores</th>
		<th>creacion</th>
		<th>estado</th>
		<th></th>
	</tr>
	<?php foreach($juegos as $juego){
		$estado = 'F';
		$jugadores = $db->select('nombre, lugar, COUNT(*) as movs',
			'jugadores j JOIN usuarios u ON u.id = j.usuario_id LEFT JOIN movimientos m ON m.jugador_id = j.id',
			array('juego_id' => $juego['id']), 'GROUP BY j.id');
		foreach($jugadores as $k => $v){
			$jugadores[$k] = $v['nombre'].($v['lugar']?' ('.$v['lugar'].')':'');
			if($estado=='F'){
				if($v['movs'] < 2) $estado = 'P';
				else if(!$v['lugar']) $estado = 'J';
			}
		}
		?>
	<tr>
		<td><?=$juego['nombre']?></td>
		<td><?=substr($juego['pista'], 7, -4)?></td>
		<td><?=implode(', ', $jugadores)?></td>
		<td><?=$juego['fecha']?></td>
		<td><?=$estados[$estado]?></td>
		<td><a href="jugar.php?juego=<?=$juego['id']?>">jugar</a></td>
	</tr>
	<? } ?>
</table>
<a href="host.php">crear juego nuevo</a>
<br/>
<br/>
<a href="sugerencias.php">sugerencias</a>

<?php pag_foot();?>