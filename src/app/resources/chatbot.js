function chatAddChatMessage(sender, message) {
	let chat = $('#chat_messages');
	if (chat.stop) {
		chat.stop();
	}
	let item = $('<div class="item ' + sender + '"><div class="avatar"></div><div class="message">' + message + '</div></div>');
	chat.append(item);

	// scroll to message
	let itemTop = chat.prop('scrollHeight');

	if (chat.animate) {
		chat.animate({
			scrollTop: itemTop
		}, 500, 'swing');
	} else {
		chat.scrollTop(itemTop);
	}
}

function chatGetUserID() {
	let cookieName = (z_auth) ? z_auth.session_token_cookie_name : 'chatbot_user_id';
	let cookieValue = getCookie(cookieName);
	if (!(cookieValue.length > 0)) {
		// create fake temporary user
		cookieValue = new Date().getTime();
		setCookie('chatbot_user_id', cookieValue, 1, '/');
	}
	return cookieValue;
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
				sender: chatGetUserID(),
				message: message
			}),
			function (data, status) {
				for(var i = 0, max = data.length; i < max; i++) {
					let messages = data[i].text.split('<br/>');
					for (message of messages) {
						chatAddChatMessage('bot', message);
					}
				}
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
		if (!z_chatbot.started) {
			chatStartConversation();
		}
		w.addClass('chat-is-open')
		$('#chat_text', w).focus();
	}
}

function chatStartConversation() {
	if (!z_chatbot.started) {
		z_chatbot.started = true;
		let messages = z_chatbot.start_message.split('<br/>');
		for (message of messages) {
			chatAddChatMessage('bot', message);
		}
	}
}

$(function() {
	if (z_chatbot.auto_start && !z_chatbot.started) {
		setTimeout(chatOpenWindow, z_chatbot.auto_start_delay * 1000);
	}
});
