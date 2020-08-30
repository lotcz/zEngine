/**
 * Append message element to chat window.
 * @param  {object} item [description]
 */
function chatAppendMessageItem(item) {
	const chat = $('#chat_messages');

	// stop animation
	if (chat.stop) {
		chat.stop();
	}

	// append element
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

/**
 * Check if queue is being processed at the moment.
 * @return {boolen} True is queue is being processed.
 */
function chatIsProcessingQueue() {
	return (z_chatbot.message_queue_timer != null);
}

/**
 * Process queue if not empty.
 */
function chatProcessMessageQueue() {
	z_chatbot.message_queue_timer = null;
	if (z_chatbot.message_queue.length > 0) {
		const item = z_chatbot.message_queue.shift();
		chatAppendMessageItem(item);
		z_chatbot.message_queue_timer = setTimeout(chatProcessMessageQueue, z_chatbot.messages_delay);
	}
}

/**
 * Add new message element to queue.
 * @param  {object} item [description]
 */
function chatQueueMessageItem(item) {
	if (!z_chatbot.message_queue) {
		z_chatbot.message_queue = [];
	}
	z_chatbot.message_queue.push(item);
	if (!chatIsProcessingQueue()) {
		chatProcessMessageQueue();
	}
}

/**
 * Queue new message for display.
 * @param  {string} sender  Sender of message (bot/user). Will become CSS class of the message element.
 * @param  {string} text Text of the message. Will be split into multiple elements if contains <br/>.
 */
function chatQueueMessage(sender, text) {
	if (text != null && text.length > 0) {
		const messages = text.split('<br/>');
		for (message of messages) {
			const trimmed = message.trim();
			if (trimmed.length > 0) {
				const item = $('<div class="item ' + sender + '"><div class="avatar"></div><div class="message">' + trimmed + '</div></div>');
				chatQueueMessageItem(item);
			}
		}
	}
}

/**
 * Return user indentifier for chatbot to keep context.
 * @return {string} [description]
 */
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

/**
 * Send user message to chat API and then process response.
 * @param {?Event} e JS event that triggered the action.
 */
function chatSendMessage(e) {
	if (e) {
		e.preventDefault();
	}

	let text_input = $('#chat_form #chat_text');
	let message = text_input.val().trim();

	if (message.length > 0) {
		chatQueueMessage('user', message);
		text_input.val('');

		$.post(
			z_chatbot.url,
			JSON.stringify({
				sender: chatGetUserID(),
				message: message
			}),
			function (data, status) {
				for(var i = 0, max = data.length; i < max; i++) {
					chatQueueMessage('bot', data[i].text);
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

/**
 * Display initial chatbot message to start the conversation.
 */
function chatStartConversation() {
	if (!z_chatbot.started) {
		z_chatbot.started = true;
		chatQueueMessage('bot', z_chatbot.start_message);
	}
}

/**
 * Open chat window and display initial chatbot message if conversation hasn't started yet.
 */
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

/**
 * Initialize chat.
 */
$(function() {
	if (z_chatbot.auto_start && !z_chatbot.started) {
		// Start timer to open chat window automatically after some time.
		setTimeout(chatOpenWindow, z_chatbot.auto_start_delay);
	}
});
