var z_chat_started = false;

function chatAddChatMessage(sender, message) {
	let chat = $('#chat_messages');
	chat.stop();
	let item = $('<div class="item ' + sender + '"><div class="avatar"></div><div class="message">' + message + '</div></div>');
	chat.append(item);
	chat.animate({
		scrollTop: item.offset().top
	}, 500, 'swing');
}

function chatSendMessage(e) {
	if (e) {
		e.preventDefault();
	}

	let text_input = $('#chat_form #chat_text');
	let message = text_input.val();

	if (message.length > 0) {
		chatAddChatMessage('user', message);
		text_input.val('');

		$.post(
			z_chatbot.url,
			JSON.stringify({
				sender: 'user',
				message: message
			}),
			function (data, status) {
				chatAddChatMessage('bot', data[0].text);
			},
			'json'
		);
	}

	return false;
}

function chatCloseWindow(e) {
	e.preventDefault();
	$('#chat_wrapper').removeClass('chat-is-open');
}

function chatOpenWindow(e) {
	let w = $('#chat_wrapper');
	if (w.hasClass('chat-is-open')) {
		chatSendMessage(e);
		$('#chat_form #chat_text', w).focus();
	} else {
		if (e) {
			e.preventDefault();
		}
		if (!z_chat_started) {
			chatStartConversation();
		}
		w.addClass('chat-is-open')
		$('#chat_text', w).focus();
	}
}

function chatStartConversation() {
	if (!z_chat_started) {
		z_chat_started = true;
		chatAddChatMessage('bot', z_chatbot.start_message);
	}
}

$(function() {
	if (z_chatbot.auto_start && !z_chat_started) {
		setTimeout(chatOpenWindow, z_chatbot.auto_start_delay * 1000);
	}
});
