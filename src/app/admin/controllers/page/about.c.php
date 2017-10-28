<?php
	$this->setData('sessions', zModel::select($this->db,'viewSessionsStats'));
