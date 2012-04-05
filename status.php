<html>
	<head>
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/websocket.js"></script>
	</head>
	<body>
		<span id="status"></span>
		<br/>
		<a href="prender.php">prender</a> - <a href="apagar.php">apagar</a>
		<pre><?php
			foreach(array('wss.out.txt', 'wss.err.txt') as $filename){
				if (file_exists($filename)) {
					echo "\n\n$filename was last modified: " . date ("F d Y H:i:s.", filemtime($filename)). "\n\n";
					echo file_get_contents($filename);
				}
			}
			?>
		</pre>
		<script type="text/javascript">
			init_socket();
		</script>
	</body>
</html>