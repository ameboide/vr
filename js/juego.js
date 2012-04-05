function init_juego(){
	init_jugadores();
	init_pista(juego, jugadores, function(){
		init_socket();

		if(yo){
			enviar({
				accion: 'join',
				id: yo.id,
				jugador: {
					id: yo.id,
					nombre: yo.nombre,
					color: yo.color
				}
			});

			if(!yo.pos){
				$('#actual').mousemove(elegir_posicion);
				$('#actual').click(posicionarme);
			}
			else if(yo.movimientos.length < juego.movs){
				$('#actual').mousemove(elegir_movimiento);
				$('#actual').click(moverme);
			}
		}

	});
}

//genera la lista de jugadores
function init_jugadores(){
	$('#lista_jugadores').html('');
	$.each(jugadores, init_jugador);

	avanzar(true);
}

function avanzar(init){
	var min = 99999999, max = 0;
	$.each(jugadores, function(){
		if(this.lugar) return true;
		var m = this.movimientos;
		if(m) m = m.length;
		else m = 0;
		if(m < min) min = m;
		if(m > max) max = m;
	});
	juego.movs = min < max ? max : max + 1;

	if(min == max && !init){
		$.each(jugadores, function(){ set_info(this); });
		init_huella(jugadores);
		if(yo && !yo.lugar && min <= max){
			if(yo.pos){
				$('#actual').mousemove(elegir_movimiento);
				$('#actual').click(moverme);

				dibujar_movimiento_temp(yo.pos, yo.dest, yo.color, yo.dest, yo.choque);
			}
			else{
				$('#actual').mousemove(elegir_posicion);
				$('#actual').click(posicionarme);
			}
		}
	}
	else{
		$.each(jugadores, function(){ set_info(this); });
	}
}

//lo agrega a la lista y le setea sus cosas
function init_jugador(id, jugador){
	if(!jugador.id) jugador.id = id;

	var m = jugador.movimientos;
	if(!m) m = jugador.movimientos = [];

	if(!jugador.pos){
		jugador.pos = m.length ? m[m.length-1] : null;
	}
	if(!jugador.choque){
		jugador.choque = jugador.choque_x !== null ? {x: jugador.choque_x, y: jugador.choque_y} : null;
	}
	if(!jugador.vel){
		jugador.vel = m.length < 2 || jugador.choque && juego.accion_choque[1] == 'F' ?
			{x: 0, y: 0} :
			{x: jugador.pos.x - m[m.length - 2].x, y: jugador.pos.y - m[m.length - 2].y};
	}
	jugador.dest = jugador.pos ? {x: jugador.pos.x + jugador.vel.x, y: jugador.pos.y + jugador.vel.y } : null;

	$('#lista_jugadores').append(
		'<li id="jugador_'+jugador.id+
			'" style="color:'+jugador.color+'" '+
			(yo && jugador.id==yo.id?'class="yo"':'')+'>'+
			jugador.nombre+
			' <span id="info_'+jugador.id+'"></span>'+
		'</li>');
}

//muestra en la lista de jugadores en q esta cada uno
function set_info(jugador){
	var info = '';
	var movs = jugador.movimientos.length;

	if(jugador.lugar) info = '#'+jugador.lugar;
	else if(!movs) info = 'posicionandose...';
	else if(movs < juego.movs) info = 'moviendo...';
	else info = 'ready';

	$('#info_'+jugador.id).html('['+info+']');

	if(jugador.online) $('#jugador_'+jugador.id).addClass('online');
	else $('#jugador_'+jugador.id).removeClass('online');
}

//ejecuta un movimiento (invisible hasta q no se muevan todos)
function mover(jugador, pos){
	//dibujar_movimiento(jugador.pos, pos, jugador.color);
	var vel = jugador.choque && juego.accion_choque[1] == 'F' ? {x: 0, y:0} :
		{x: pos.x - jugador.pos.x, y: pos.y - jugador.pos.y};
	jugador.vel = vel;
	jugador.pos = pos;
	jugador.dest = {x: pos.x + vel.x, y: pos.y + vel.y };
	jugador.movimientos.push(pos);

	avanzar();
}

//se pone en la partida (invisible hasta q no se pongan todos)
function posicion_inicial(jugador, pos){
	play('partir');
	//dibujar_movimiento(pos, pos, jugador.color);
	jugador.pos = jugador.dest = pos;
	jugador.movimientos = [pos];

	avanzar();
}

//pasando el mouse por la pista para elegir la partida
function elegir_posicion(evt){
	var pos = pos_evento(evt);
	dibujar_movimiento_temp(pos, pos, yo.color);
}

//click para elegir punto de partida
function posicionarme(evt){
	var pos = pos_evento(evt);
	var cruce = revisar_cruce(pos, pos);
	if(cruce.choque){
		alert('hay que partir dentro de la pista');
		return;
	}

	enviar({
		accion: 'posicion',
		id: yo.id,
		pos: pos
	});

	$('#actual').unbind('mousemove');
	$('#actual').unbind('click');

	posicion_inicial(yo, pos);
}

//pasando el mouse por la pista para elegir el proximo movimiento
function elegir_movimiento(evt){
	var pos = pos_evento(evt);
	if(Math.abs(yo.dest.x - pos.x) > 1 || Math.abs(yo.dest.y - pos.y) > 1) return;

	var choque = yo.choque ? yo.choque : revisar_cruce(yo.pos, pos).choque;
	dibujar_movimiento_temp(yo.pos, pos, yo.color, yo.dest, choque);
}

//click para elegir movimiento
function moverme(evt){
	var pos = pos_evento(evt);
	if(Math.abs(yo.dest.x - pos.x) > 1 || Math.abs(yo.dest.y - pos.y) > 1) return;

	$('#actual').unbind('mousemove');
	$('#actual').unbind('click');

	if(yo.choque && juego.accion_choque[0] == 'V'){ //revisar si volvi a la pista
		if(pos.x == yo.choque.x && pos.y == yo.choque.y) yo.choque = null;
		play('mover');
	}
	else{ //revisar si pase por la meta, choque, o volvi de un choque por donde sali
		var cruce = revisar_cruce(yo.pos, pos, yo.choque);

		if(cruce.meta) pasar_meta();
		if(cruce.choque){
			if(!yo.choque) play('choque');
			//entrar por donde salio, o volver donde estaba antes
			yo.choque = juego.accion_choque[0] == 'E' ? cruce.choque : yo.pos;
		}
		else{
			yo.choque = '';
			play('mover');
		}
	}

	if(pos.x < 0 || pos.y < 0 ||
		pos.x*porte_grilla > porte_pista.w ||
		pos.y*porte_grilla > porte_pista.h)
		yo.lugar = -1;

	enviar({
		accion: 'mover',
		id: yo.id,
		pos: pos,
		lugar: yo.lugar,
		vueltas: yo.vueltas,
		choque: yo.choque
	});

	mover(yo, pos);
}

function pasar_meta(){
	if(yo.vueltas) play('meta');
	yo.vueltas++;
	if(yo.vueltas == juego.vueltas){
		var lugar = 1;
		for(var j in jugadores){
			if(jugadores[j].lugar > 0) lugar++;
		}
		yo.lugar = lugar;
	}
}

function pos_evento(evt){
	var off = $(evt.target).offset();
	return {
		x: Math.round((evt.pageX - off.left) / porte_grilla),
		y: Math.round((evt.pageY - off.top) / porte_grilla)
	};
}

function play(nombre){
	try{$('#audio_'+nombre)[0].play();}
	catch(e){}
}