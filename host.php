<?php
require_once 'base.php';
sesion();
if(!empty($_POST['nombre'])){
	if($db->campo('id', 'juegos', array('nombre' => $_POST['nombre']))){
		echo 'ya existe un juego con este nombre';
	}
	else{
		$id = $db->insert('juegos', $_POST);
		$db->insert('jugadores', array(
			'juego_id' => $id,
			'usuario_id' => $_SESSION['usuario']['id']));

		redir('jugar.php?juego='.$id);
	}
}

pag_head('hostear juego');
?>
<form action="host.php" method="POST">
	nombre: <input id="nombre" name="nombre" value="<?=uniqid()?>"/>
	vueltas: <input id="vueltas" name="vueltas" value="1"/>
	al chocar:
	<select name="accion_choque">
		<option value="EF">entrar por donde se salio, frenando</option>
		<option value="ES">entrar por donde se salio, sin frenar</option>
		<option value="VF">volver a la posicion anterior al choque, frenando</option>
		<option value="VS">volver a la posicion anterior al choque, sin frenar</option>
	</select>
	<br/>
	pista: <select id="nombre_pista" name="pista">
		<option></option>
		<?php
			$pistas = glob('pistas/*.png');
			foreach($pistas as $pista){ ?>
				<option value="<?=$pista?>"><?=substr($pista,7, -4)?></option>
			<?php }
		?>
		</select>
	escala (%): <input id="escala" name="escala" value="100"/>
	porte grilla: <input id="porte" name="porte" value="10"/>
	cuadros de borde: <input id="borde" name="borde" value="5"/>
	<button>crear</button>
</form>
<div id="contenedor">
	<canvas id="pista"></canvas>
</div>
<script type="text/javascript">
	$(function(){
		$('#nombre_pista, #porte, #escala, #borde').change(function(){
			init_pista({
				pista: $('#nombre_pista').val(),
				porte: $('#porte').val(),
				escala: $('#escala').val(),
				borde: $('#borde').val()
			});
		});
	});
</script>
<?php pag_foot();?>