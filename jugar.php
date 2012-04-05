<?php
require_once 'base.php';
sesion();

$juego = $db->select('*', 'juegos', array('id' => $_REQUEST['juego']));
if(empty($juego)) redir('.');
$juego = $juego[0];

$lista_jugadores = $db->select('u.nombre, j.*',
	'jugadores j JOIN usuarios u ON u.id = j.usuario_id',
	array('juego_id' => $juego['id']), 'order by id asc');

$yo = 0;
$jugando = false;
foreach($lista_jugadores as $jugador){
	$id = $jugador['id'];

	$jugador['movimientos'] = $db->select('pos_x as x, pos_y as y',
		'movimientos',
		array('jugador_id' => $id), 'order by id asc');
	if(count($jugador['movimientos']) > 1) $jugando = true;

	$jugadores[$id] = $jugador;
	if($jugador['usuario_id'] == $_SESSION['usuario']['id']) $yo = $id;
}

$chats = $db->select('*', 'chats', array('juego_id' => $juego['id']), 'order by id desc');

pag_head($juego['nombre']);
?>
<div id="div_juego">
	<span id="status"></span>
	<ul id="lista_jugadores"></ul>
	<?php if(!$yo && !$jugando){ ?>
	<a id="unirme" href="unirse.php?juego=<?=$juego['id']?>">unirme</a>
	<?php } ?>
</div>
<div id="div_chat">
	<?php if($yo){ ?>
	<span>chat: </span>
	<input type="text" id="msg_chat"/>
	<button id="btn_chat">enviar</button>
	<?php } ?>
	<ul id="chat">
		<?php foreach($chats as $chat){ ?>
		<li style="color:<?=$jugadores[$chat['jugador_id']]['color']?>">
			[<?=$chat['fecha']?>] <?=$jugadores[$chat['jugador_id']]['nombre']?>:
			<?=$chat['txt']?>
		</li>
		<?php } ?>
	</ul>
</div>

<div id="contenedor">
	<canvas id="pista"></canvas>
	<canvas id="huella"></canvas>
	<canvas id="actual"></canvas>
</div>

<audio id="audio_join" preload>
	<source src="audio/holavengoarevolearlaspatas.ogg">
	<source src="audio/holavengoarevolearlaspatas.mp3">
</audio>
<audio id="audio_partir" preload>
	<source src="audio/vroom.ogg">
	<source src="audio/vroom.mp3">
</audio>
<audio id="audio_mover" preload>
	<source src="audio/nniium.ogg">
	<source src="audio/nniium.mp3">
</audio>
<audio id="audio_choque" preload>
	<source src="audio/niipujj.ogg">
	<source src="audio/niipujj.mp3">
</audio>
<audio id="audio_meta" preload>
	<source src="audio/yoohoo.ogg">
	<source src="audio/yoohoo.mp3">
</audio>

<script type="text/javascript">
	var juego = <?=json_encode($juego)?>;
	var jugadores = <?=json_encode($jugadores)?>;
	var yo = <?=$yo ? "jugadores[$yo]" : 'null'?>;

	$(init_juego);
	$(init_chat);
</script>
<?php pag_foot();?>