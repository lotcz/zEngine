<?php
	$this->setPageTitle('Jobs');

	$this->requireModule('jobs');
	$this->setData('jobs', $this->z->jobs->listJobs());

