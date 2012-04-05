<?php
require_once 'base.php';
sesion();

if($_POST['txt']){
	$db->insert('sugerencias', array(
		'txt' => $_POST['txt'],
		'usuario_id' => $_SESSION['usuario']['id']));
}

pag_head('sugerencias');
?>
<form action="sugerencias.php" method="POST">
<textarea name="txt"></textarea>
<button type="submit">sugerir</button>
</form>
<hr/>
<ol>
	<?php
	$sugs = $db->select('u.nombre, s.*', 'sugerencias s join usuarios u on u.id = s.usuario_id', '', 'order by id ASC');
	foreach($sugs as $s){?>
	<li>[<?=$s['nombre']?><?=$s['fecha']!='0000-00-00 00:00:00' ? ' - ' . $s['fecha'] : ''?>]
		<pre><?=$s['txt']?></pre>
		<em><?=$s['status']?></em>
		<hr/>
	</li>
	<?php } ?>
</ol>
<?php pag_foot(); ?>