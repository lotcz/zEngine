<?php
	$this->setData('sessions', zModel::select($this->z->db, 'view_session_stats'));
