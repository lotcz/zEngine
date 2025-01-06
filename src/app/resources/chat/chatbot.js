/**
 * Append message element to chat window.
 * @param  {object} item [description]
 */
function chatAppendMessageItem(item) {
	const chat = z.getById('chat_messages');

	// append element
	chat.append(item);
}


/**
 * Process queue if not empty.
 */
function chatProcessMessageQueue() {
	z_chatbot.message_queue_timer = null;
	if (z_chatbot.message_queue.length > 0) {
		const item = z_chatbot.message_queue.shift();

		z_chatbot.message_queue_timer = setTimeout(chatProcessMessageQueue, z_chatbot.messages_delay);
	}
}

/**
 * Add new message element to queue.
 * @param  {object} item [description]
 */
function chatQueueMessageItem(item) {
	chatAppendMessageItem(item);
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
				const item = document.createElement('div');
				item.innerHTML = '<div class="item ' + sender + '"><div class="avatar"></div><div class="message">' + trimmed + '</div></div>';
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
	if (!z_chatbot.session_id) {
		let cookieValue = '';
		if (z_auth !== undefined) {
			cookieValue = getCookie(z_auth.session_token_cookie_name);
		}
		if (!(cookieValue.length > 0)) {
			cookieValue = getCookie('chatbot_user_id');
		}
		if (!(cookieValue.length > 0)) {
			// create fake temporary user
			cookieValue = new Date().getTime();
			setCookie('chatbot_user_id', cookieValue, 1, '/');
		}
		z_chatbot.session_id = cookieValue;
	}
	return z_chatbot.session_id;
}

/**
 * Send user message to chat API and then process response.
 * @param {?Event} e JS event that triggered the action.
 */
function chatSendMessage(e) {
	if (e) {
		e.preventDefault();
	}

	const text_input = z.getById('chat_text');
	const message = z.val('chat_text');

	if (message.length > 0) {
		chatQueueMessage('user', message);
		text_input.value = '';

		z.fetch(
			z_chatbot.url,
			{
				sender: chatGetUserID(),
				message: message
			},
			'POST'
		).then(function (response) {
			chatQueueMessage('bot', response.json.response);
		});
	}

	return false;
}

function chatCloseWindow(e) {
	e.preventDefault();
	z.removeClass('chat_wrapper', 'chat-is-open');
}

/**
 * Display initial chatbot message to start the conversation.
 */
function chatStartConversation() {
	if (z.notEmpty(z_chatbot.start_message) && !z_chatbot.started) {
		z_chatbot.started = true;
		chatQueueMessage('bot', z_chatbot.start_message);
	}
}

/**
 * Open chat window and display initial chatbot message if conversation hasn't started yet.
 */
function chatOpenWindow(e) {
	if (z.hasClass('chat_wrapper', 'chat-is-open')) {
		chatSendMessage(e);
	} else {
		if (e) {
			e.preventDefault();
		}
		if (!z_chatbot.started) {
			chatStartConversation();
		}
		z.addClass('chat_wrapper', 'chat-is-open')
	}
	z.getById('chat_text').focus();
}

/**
 * Auto starts chat if not started yet.
 */
function chatAutoStart() {
	if (!z_chatbot.started) {
		chatOpenWindow();
	}
}

/**
 * Initialize chat.
 */
document.addEventListener(
	'load',
	function () {
		if (z_chatbot.auto_start && !z_chatbot.started) {
			// Start timer to open chat window automatically after some time.
			setTimeout(chatAutoStart, z_chatbot.auto_start_delay);
		}
	}
);
