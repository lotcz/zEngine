<?php

require_once __DIR__ . '/../models/calendar_reservation.m.php';

class calendarModule extends zModule {

	public $depends_on = ['resources'];

	public function onEnabled() {

	}

	function OnBeforeRender() {
		$this->z->core->includeCSS('resources/calendar/calendar.css', 'head');
		$this->z->core->includeCSS('resources/calendar/calendar.css', 'admin.head');
	}

	function renderCalendar($name = 'calendar-main', $admin = false) {
		?>
			<div id="<?=$name?>" class="calendar">
				<div class="placeholder-wave mb-3">
					<span class="placeholder placeholder-lg col-4"></span>
				</div>
				<div class="card">
					<div class="spinner-border text-warning my-5 mx-auto p-5" role="status">
						<span class="visually-hidden">Nahrávám...</span>
					</div>
				</div>
			</div>
			<script type="module" defer>
				import Calendar from '/resources/calendar/calendar.js';
				const calendar = new Calendar(document.getElementById('<?=$name?>'), <?=$admin ? 'true' : 'false'?>);
			</script>
		<?php
	}

	function loadReservations($from, $to) {
		$is_admin = $this->z->admin->isAuth();
		$reservations = CalendarReservationModel::select(
			$this->z->db,
			$is_admin ? 'viewCalendarReservations' : 'calendar_reservation',
			'calendar_reservation_start > ? and calendar_reservation_start < ?',
			'calendar_reservation_start',
			null,
			[$from, $to],
			[PDO::PARAM_STR, PDO::PARAM_STR]
		);

		if (!$is_admin) {
			$user_id = $this->z->auth->isAuth() ? $this->z->auth->user->iget('user_id') : 0;
			if ($user_id) {
				foreach ($reservations as $reservation) {
					if ($reservation->iget('calendar_reservation_user_id') === $user_id) {
						$reservation->set('email', $this->z->auth->user->get('user_email'));
					}
				}
			}
		}
		return zModel::toJson($reservations);
	}

	function loadReservationById($id) {
		$res = new CalendarReservationModel($this->z->db, $id);
		return $res->is_loaded ? $res : null;
	}

	function saveReservation($id, $user_id, $start, $service_id, $duration) {
		if ($user_id !== $this->z->auth->user->ival('user_id') && !$this->z->admin->isAdmin()) {
			throw new Exception("Access Forbidden!");
		}
		$res = null;
		if ($id) {
			$res = $this->loadReservationById($id);
		}
		if (!$res) {
			$res = new CalendarReservationModel($this->z->db);
		}
		$res->set('calendar_reservation_user_id', $user_id);
		$res->set('calendar_reservation_start', $start);
		$res->set('calendar_reservation_cosmetic_service_id', $service_id);
		$res->set('calendar_reservation_duration', $duration);
		$res->save();
		return $res;
	}
}
