<?php

	$endpoint = z::get('endpoint');
	if (empty($endpoint)) {
		throw new \Exception('Endpoint not specified');
	}

	$this->z->core->requireModule('autocomplete');
	$endpointConfig = $this->z->autocomplete->getEndpointConfig($endpoint);

	$table = $endpointConfig['select_table'];
	$idName = $endpointConfig['select_id_field'];
	$labelName = $endpointConfig['select_label_field'];

	$id = z::get('id');
	if (isset($id)) {
		$where = "{$idName} = ?";
		$bindings = [$id];
		$types = [PDO::PARAM_INT];
	} else {
		$search = z::get('search');
		if (!empty($search)) {
			$where = "{$labelName} LIKE ?";
			$bindings = ["%{$search}%"];
			$types = [PDO::PARAM_STR];
		} else {
			$where = null;
			$bindings = [];
			$types = [];
		}
	}

	$whereSql = empty($where) ? '' : ' WHERE ' . $where;

	$result = zModel::selectSql(
		$this->z->db,
		"SELECT {$idName}, {$labelName} FROM {$table} {$whereSql} LIMIT 10",
		$bindings,
		$types
	);

	$json = zModel::toJson($result);

	$this->setData('json', (isset($id)) ? $json[0] ?? null : $json);
