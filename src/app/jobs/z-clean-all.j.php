<?php

	$this->z->jobs->executeJob('z-clean-form-tokens');
	$this->z->jobs->executeJob('z-clean-sessions');
	$this->z->jobs->executeJob('z-clean-emails');

