<html>
	<head>
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/websocket.js"></script>
	</head>
	<body>
		<span id="status"></span>
		<script type="text/javascript">
			init_socket();
			enviar({'apagar':'apagar'});
		</script>
	</body>
</html>