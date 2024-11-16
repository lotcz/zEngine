<rss version="2.0">
	<channel>
		<title><?=$this->getFullPageTitle() ?></title>
		<link rel="self"><?=$this->url('rss') ?></link>
		<description><?=$this->getConfigValue('site_description') ?></description>

		<?php
			$this->renderPageView();
		?>
	</channel>
</rss>
