<?php

require_once __DIR__ . '/../classes/model.php';

class CalendarReservationModel extends zModel {

	public $table_name = 'calendar_reservation';

	public function getStart(): DateTime {
		return new DateTime($this->val('calendar_reservation_start'));
	}

	public function getEnd(): DateTime {
		$duration = $this->ival('calendar_reservation_duration');
		return $this->getStart()->add(new DateInterval("PT{$duration}M"));
	}

}
