<?php
	$this->setData('sessions', zModel::select($this->z->db,'viewSessionsStats'));
