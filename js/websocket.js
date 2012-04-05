var socket;
var cola_socket = [];

function init_socket(){
	var status = $('#status');
	if(status) status.html('conectando...');

	var pars = [];
	if(window.juego){
		pars.push('juego='+juego.id);
		if(yo) pars.push('id='+yo.id);
	}
	var host = 'ws://'+document.location.host+':12345/vr?'+pars.join('&');
	try{
		socket = window.WebSocket ? new WebSocket(host) : new MozWebSocket(host);
		socket.onmessage = recibir;
		socket.onopen = function(){
			var cola = cola_socket;
			cola_socket = null;
			for(var i=0; i<cola.length; i++) enviar(cola[i]);
			if(status) status.html('conectado ok');

		};
		socket.onclose = function(){
			alert('servidor apagado, igual puedes jugar solo :foreveralone');
			if(status) status.html('conexion cerrada');
		};
	}
	catch(ex){
		console.log(ex);
		alert('tu browser es demasiado indigno para jugar, usa firefox o chrome');
		if(status) status.html('conexion fail');
	}
}

function enviar(data){
	if(cola_socket){
		cola_socket.push(data);
		return;
	}
	try{
		socket.send(JSON.stringify(data));
	} catch(ex){ console.log(ex); }
}

function quit(){
	socket.close();
	socket=null;
}

function recibir(msg){
	//console.log(msg);
	var data = JSON.parse(msg.data);
	//console.log(data);
	if(yo && data.id == yo.id) return;
	var jugador = jugadores[data.id];
	switch(data.accion){
		case 'posicion':
			posicion_inicial(jugador, data.pos);
			break;
		case 'mover':
			var v = jugador.vueltas;
			var c = jugador.choque;
			$.each(['vueltas', 'choque', 'lugar'], function(){
				if(data[this] !== undefined) jugador[this] = data[this];
			});

			if(!c && jugador.choque) play('choque');
			else if(v && jugador.vueltas > v) play('meta');
			else play('mover');

			mover(jugador, data.pos);
			break;
		case 'join':
			if(!jugador){
				jugadores[data.id] = data.jugador;
				init_jugador(data.id, data.jugador);
			}
			else{
				jugador.online = true;
				set_info(jugador);
			}
			play('join');
			break;
		case 'lista_online':
			$.each(data.ids, function(){
				jugadores[this].online = true;
				set_info(jugadores[this]);
			});
			break;
		case 'chat':
			escribir_chat(data.id, data.msg);
			break;
	}
}