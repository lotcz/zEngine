<div id="chat_wrapper">
	<div id="chat_window">
		<div id="chat_close" onclick="chatCloseWindow(event)">
			<div>
				<span>x</span>
			</div>
		</div>

		<div id="chat_messages">
		</div>

		<form id="chat_form" action="#" onsubmit="chatSendMessage(event)">
			<input name="chat_text" id="chat_text" class="form-control" />
		</form>
	</div>

	<a href="#" id="chat_icon" onclick="chatOpenWindow(event)">
		<img src=<?=$this->url('resources/chat.svg') ?> alt="chat" />
	</a>
</div>
