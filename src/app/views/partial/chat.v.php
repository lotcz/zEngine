<div id="chat_wrapper" >
	<div id="chat_window">
		<div id="chat_close" onclick="chatCloseWindow(event)">
		</div>

		<div id="chat_header">
			<div id="chat_bot_icon">
			</div>
			<div id="chat_bot_name">
				<?=$this->z->chatbot->getConfigValue('bot_name', 'Bot'); ?>
			</div>
			<div id="chat_slogan">
				<?=$this->z->chatbot->getConfigValue('slogan', 'Bot'); ?>
			</div>
		</div>

		<div id="chat_messages">
		</div>

		<form id="chat_form" action="#" onsubmit="chatSendMessage(event)">
			<input name="chat_text" id="chat_text" class="form-control" />
		</form>
	</div>

	<a href="#" id="chat_icon_wrapper" onclick="chatOpenWindow(event)">
		<div class="chat-icon">
		</div>
	</a>
</div>
