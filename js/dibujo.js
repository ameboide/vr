var porte_grilla = 10;
var porte_pista = {w: 10, h: 10};

var ctx_pista = null;
var ctx_huella = null;
var ctx_actual = null;

function dibujar_grilla(){
	var canvas = document.createElement('canvas');
	canvas.width = canvas.height = porte_grilla;
	var ctx = canvas.getContext('2d');

	ctx.strokeStyle = '#aaa';
	ctx.beginPath();
	ctx.moveTo(0, porte_grilla);
	ctx.lineTo(0, 0);
	ctx.lineTo(porte_grilla, 0);
	ctx.stroke();

	$('#pista').css('backgroundImage', 'url("'+canvas.toDataURL("image/png")+'")');
}

function init_pista(juego, jugadores, callback){
	porte_grilla = parseInt(juego.porte);

	ctx_pista = $('#pista')[0].getContext('2d');

	// Draw shapes
	var img = new Image();
	img.src = juego.pista;

	$(img).load(function(){
		var borde = porte_grilla * juego.borde;
		porte_pista = {
			w: img.width * juego.escala / 100 + borde * 2,
			h: img.height * juego.escala / 100 + borde * 2};
		$('#contenedor canvas').attr({'width': porte_pista.w, 'height': porte_pista.h});
		ctx_pista.drawImage(img, borde, borde, porte_pista.w - borde * 2, porte_pista.h - borde * 2);

		if(jugadores){
			ctx_huella = $('#huella')[0].getContext('2d');
			ctx_actual = $('#actual')[0].getContext('2d');
			init_huella(jugadores);
		}
		if(callback) callback();
	});

	dibujar_grilla();
}

function init_huella(jugadores){
	limpiar(ctx_huella);
	for(var j in jugadores){
		var movs = jugadores[j].movimientos;
		if(movs && movs.length){
			var color = $('#jugador_'+j).css('color').replace('(', 'a(').replace(')', ', #)');
			var pos = movs[movs.length-1];

			var a = 0.8;
			dibujar_movimiento(pos, pos, color.replace('#', 1));
			for(var m=movs.length-1; m>0; m--){
				if(a > 0.05) a*=.75;
				dibujar_movimiento(movs[m], movs[m-1], color.replace('#', a));
			}
		}
	}
}

function circulo(ctx, x, y, radio, fill){
	ctx.beginPath();
	ctx.arc(x*porte_grilla, y*porte_grilla, radio, 0, Math.PI*2);
	ctx.closePath();
	ctx.stroke();
	if(fill) ctx.fill();
}

function dibujar_movimiento(desde, hasta, color, ctx, pos_grilla, choque){
	if(!ctx) ctx = ctx_huella;
	if(color) ctx.strokeStyle = ctx.fillStyle = color;

	if(desde != hasta){
		ctx.lineWidth = 3;
		ctx.beginPath();
		ctx.moveTo(desde.x*porte_grilla, desde.y*porte_grilla);
		ctx.lineTo(hasta.x*porte_grilla, hasta.y*porte_grilla);
		ctx.stroke();
		ctx.lineWidth = 1;
	}

	circulo(ctx, hasta.x, hasta.y, 4, true);

	if(pos_grilla){
		for(var x = pos_grilla.x-1; x<=pos_grilla.x+1; x++){
			for(var y = pos_grilla.y-1; y<=pos_grilla.y+1; y++){
				circulo(ctx, x, y, 2);
			}
		}
	}

	if(choque){
		circulo(ctx, choque.x, choque.y, porte_grilla);
	}
}

function dibujar_movimiento_temp(desde, hasta, color, pos_grilla, choque){
	limpiar();
	dibujar_movimiento(desde, hasta, color, ctx_actual, pos_grilla, choque);
}

function limpiar(ctx){
	if(!ctx) ctx = ctx_actual;
	ctx.clearRect(0, 0, porte_pista.w, porte_pista.h);
}

function revisar_cruce(a, b, choque){
	var ax = a.x*porte_grilla;
	var ay = a.y*porte_grilla;
	var bx = b.x*porte_grilla;
	var by = b.y*porte_grilla;

	var l = Math.min(ax, bx);
	var t = Math.min(ay, by);
	var w = Math.abs(ax - bx) + 1;
	var h = Math.abs(ay - by) + 1;

	var data = ctx_pista.getImageData(l, t, w, h).data;

	if(w > h) return revisar_alfa(data, ax, ay, bx, by, l, t, w, h, choque, true);
	return revisar_alfa(data, ay, ax, by, bx, l, t, w, h, choque, false);
}

function revisar_alfa(data, ax, ay, bx, by, l, t, w, h, choque, horizontal){
	var meta = 0;
	if(choque){
		var cx = choque.x*porte_grilla;
		var cy = choque.y*porte_grilla;
	}

	var i = ax < bx ? 1 : -1;
	var m = ax==bx ? 0 : (by - ay) / (bx - ax);
	var y0 = ay - ax * m;
	for(var x = ax; x != bx+i; x+=i){
		var y = Math.round(y0 + x * m);
		var xx = horizontal ? x : y;
		var yy = horizontal ? y : x;
		var a = data[(yy-t)*w*4 + (xx-l)*4 + 3];

		if(choque){
			if((cx-xx)*(cx-xx) + (cy-yy)*(cy-yy) <= porte_grilla*porte_grilla){
				if(a==1 || a==254 || a==250 || a==251) choque = null;
			}
		}

		if(!choque){
			switch(a){
				case 1: //pista
				case 254:
					break;
				case 250: //meta 1
					if(!meta) meta = 1;
					break;
				case 251: //meta 2
					if(meta == 1) meta = 2;
					break;
				default: //obstaculo
					var cx = Math.round(x/porte_grilla) - i;
					var cy = Math.round((y0 + cx*porte_grilla * m)/porte_grilla);
					return {
						meta: meta == 2,
						choque: horizontal ? {x: cx, y: cy} : {x: cy, y: cx}
					};
			}
		}
	}

	return {meta: meta == 2, choque: choque};
}