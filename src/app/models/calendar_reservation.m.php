<?php

require_once __DIR__ . '/../classes/model.php';

class CalendarReservationModel extends zModel {

	public $table_name = 'calendar_reservation';

	public $ignored_columns = ['calendar_reservation_end'];

	public function getStart(): DateTime {
		return new DateTime($this->val('calendar_reservation_start'));
	}

	public function getEnd(): DateTime {
		$duration = $this->ival('calendar_reservation_duration');
		$interval = $this->bval('calendar_reservation_whole_day') ? "P{$duration}D" : "PT{$duration}M";
		return $this->getStart()->add(new DateInterval($interval));
	}

}
