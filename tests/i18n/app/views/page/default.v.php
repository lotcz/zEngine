<h1><?=$this->z->i18n->translate('Hello') ?></h1>
<p><?=$this->t('This is a test site for zEngine %s module.', '<strong>i18n</strong>') ?></p>
<p>Date: <strong><?=$this->formatDate(time()) ?></strong></p>
<p>Date and time: <strong><?=$this->formatDatetime(time()) ?></strong></p>
<p>Decimal number: <strong><?=$this->formatDecimal(1854.789,3) ?></strong></p>
<p>Integer number: <strong><?=$this->formatInteger(25000459878453) ?></strong></p>
<p>Money: <strong><?=$this->formatMoney(650.85) ?></strong></p>
<p>Money with conversion: <strong><?=$this->formatMoney($this->convertMoney(650.85)) ?></strong></p>

<ul>
	<?php
		foreach ($this->data['languages'] as $language) {
			echo sprintf('<li><a href="%s?language_id=%d">%s</a>', $this->base_url, $language->val('language_id'), $language->val('language_name'));
		}
	?>
</ul>

<ul>
	<?php
		foreach ($this->data['currencies'] as $currency) {
			echo sprintf('<li><a href="%s?currency_id=%d">%s</a>', $this->base_url, $currency->val('currency_id'), $currency->val('currency_name'));
		}
	?>
</ul>
