<h1><?=$this->z->i18n->translate('Hello') ?></h1>
<p><?=$this->t('This is a test site for zEngine %s module.', '<strong>i18n</strong>') ?></p>
<p>Date: <strong><?=$this->formatDate(time()) ?></strong></p>
<p>Date and time: <strong><?=$this->formatDatetime(time()) ?></strong></p>
<p>Decimal number: <strong><?=$this->formatDecimal(1854.789,3) ?></strong></p>
<p>Integer number: <strong><?=$this->formatInteger(25000459878453) ?></strong></p>
<p>Money: <strong><?=$this->formatMoney(650.85) ?></strong></p>

<ul>
	<?php
		foreach ($this->data['languages'] as $language) {
			echo sprintf('<li><a href="#" onclick="javascript:setLang(\'%s\');">%s</a>', $language->val('language_code'), $language->val('language_name'));
		}
	?>
</ul>

<ul>
	<?php
		foreach ($this->data['currencies'] as $currency) {
			echo sprintf('<li><a href="#" onclick="javascript:setLang(\'%s\');">%s</a>', $currency->val('currecy_id'), $currency->val('currency_name'));
		}
	?>
</ul>

<script>

// set cookie value
function setCookie(cname, cvalue, exdays, path) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = 'expires='+d.toUTCString();
    document.cookie = cname + '=' + cvalue + '; ' + expires + ';path=' + path;
}

// change language
function setLang(lang) {
	setCookie('language', lang, 365, '/');
	document.location = document.location;
}

</script>
