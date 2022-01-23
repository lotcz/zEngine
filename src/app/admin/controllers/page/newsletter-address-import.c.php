<?php
	$this->setPageTitle('Import Addresses');

	if (!$this->z->isModuleEnabled('newsletter')) {
		$this->z->messages->error('Newsletter module is not enabled!');
		return;
	}

	if (z::isPost()) {
		$text = z::get('import_addresses');
		list($imported, $total) = $this->z->newsletter->importSubscriptions($text);
		$this->message(sprintf('Imported %d addresses from total number of %d.', $imported, $total), 'success');
	}
