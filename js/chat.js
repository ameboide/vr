function init_chat(){
	$('#btn_chat').click(enviar_chat);
	$('#msg_chat').keyup(function(evt){
		if(evt.keyCode == 13) enviar_chat();
	});
}

function enviar_chat(){
	var msg = $('#msg_chat').val().trim();
	if(!msg) return;
	escribir_chat(yo.id, msg);
	enviar({
		accion: 'chat',
		id: yo.id,
		msg: msg
	});
	$('#msg_chat').val('');
}

function escribir_chat(id, msg){
	var j = jugadores[id];
	var fecha = new Date(Date.now()-new Date().getTimezoneOffset()*60000).toISOString().replace('T',' ').replace(/\..+/, '');
	$('#chat').prepend('<li style="color:'+j.color+'">['+fecha+'] '+j.nombre+': '+msg+'</li>');
}