<?php
	require_once __DIR__ . '/../../models/test.m.php';

	$this->setPageTitle('Můj profil');
	
	if ($this->isCustAuth() && !$this->z->custauth->isAnonymous()) {
		$chart_data = [];
		$customer = $this->getCustomer();	
		
		$tests = zModel::Select(
		/* db */		$this->db,
		/* table */		'belbin_tests',
		/* where */		'belbin_test_customer_id = ? and belbin_test_end_date is not null',
		/* bindings */	[$customer->ival('customer_id')],
		/* types */		'i',
		/* paging */	null,
		/* orderby */	'belbin_test_start_date DESC'
		);
		
		foreach ($tests as $test) {
			$test->results = zModel::Select(
			/* db */		$this->db,
			/* table */		'viewBelbinTestResults',
			/* where */		'belbin_test_id = ?',
			/* bindings */	[$test->ival('belbin_test_id')],
			/* types */		'i',
			/* paging */	null,
			/* orderby */	'score DESC'
			);
			$chart_data[] = [
				'test_id' => $test->ival('belbin_test_id'),
				'data' => [
					'datasets' => [[
						'data' => zModel::columnAsArray($test->results, 'score', 'i'),
						'backgroundColor' => zModel::columnAsArray($test->results, 'belbin_role_color'),
						'borderWidth' => 0
					]],
					'labels' => zModel::columnAsArray($test->results, 'belbin_role_name')
				]				
			];
		}
		$this->setData('tests', $tests);
		$this->insertJS(['chart_data' => $chart_data]);
		$this->includeJS('profil.js');
	} else {
		$this->redirect('login');
	}
  
