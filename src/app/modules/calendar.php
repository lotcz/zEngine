<?php

require_once __DIR__ . '/../models/calendar_reservation.m.php';

class calendarModule extends zModule {

	public array $depends_on = ['resources'];

	public function onEnabled() {

	}

	function onBeforeRender() {
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

	/**
	 * @param $from
	 * @param $to
	 * @return CalendarReservationModel[]
	 */
	function loadReservations(DateTime $from, DateTime $to): array {
		$is_admin = $this->z->admin->isAdmin();
		return CalendarReservationModel::select(
			$this->z->db,
			$is_admin ? 'view_calendar_reservations' : 'calendar_reservation',
			'calendar_reservation_end > ? and calendar_reservation_start < ?',
			'calendar_reservation_start',
			null,
			[z::mysqlDatetime($from), z::mysqlDatetime($to)],
			[PDO::PARAM_STR, PDO::PARAM_STR]
		);
	}

	function loadReservationsJson(DateTime $from, DateTime $to): array {
		$reservations = $this->loadReservations($from, $to);
		$is_admin = $this->z->admin->isAdmin();
		if (!$is_admin) {
			$user_id = $this->z->auth->isAuth() ? $this->z->auth->user->ival('user_id') : 0;
			if ($user_id) {
				foreach ($reservations as $reservation) {
					if ($reservation->ival('calendar_reservation_user_id') === $user_id) {
						$reservation->set('email', $this->z->auth->user->val('user_email'));
					}
				}
			}
		}

		return zModel::toJson($reservations);
	}

	function loadReservationById($id): ?CalendarReservationModel {
		$res = new CalendarReservationModel($this->z->db, $id);
		return $res->is_loaded ? $res : null;
	}

	function deleteReservationById($id) {
		$res = new CalendarReservationModel($this->z->db, $id);
		if (!$res->is_loaded) return;
		if ($res->ival('calendar_reservation_user_id') !== $this->z->auth->user->ival('user_id') && !$this->z->admin->isAdmin()) {
			throw new Exception("Access Forbidden!");
		}
		$res->delete();
	}

	function conflictsExists(DateTime $start, DateTime $end, int $exclude = null): bool {
		$conflicting = $this->loadReservations($start, $end);

		foreach ($conflicting as $reservation) {
			if ($exclude !== null && $reservation->ival('calendar_reservation_id') === $exclude) {
				continue;
			}
			return true;
		}

		return false;
	}

	function saveReservation(?int $id, int $user_id, DateTime $start, int $service_id, int $duration, bool $whole_day) {
		if ($user_id !== $this->z->auth->user->ival('user_id') && !$this->z->admin->isAdmin()) {
			throw new Exception($this->z->core->t("Access Forbidden!"));
		}

		$dayOfWeek = intval($start->format('w'));
		if ($dayOfWeek < 1 || $dayOfWeek > 5) {
			throw new Exception($this->z->core->t("Invalid day!"));
		}

		if ($whole_day) {
			$start->setTime(0,0);
		}
		$end = clone $start;
		$end = $end->add(new DateInterval($whole_day ? "P{$duration}D" : "PT{$duration}M"));

		if ($this->conflictsExists($start, $end, $id)) {
			throw new Exception($this->z->core->t("Conflict Exists!"));
		}

		$res = null;
		if ($id) {
			$res = $this->loadReservationById($id);
		}
		if (!$res) {
			$res = new CalendarReservationModel($this->z->db);
		}
		$res->set('calendar_reservation_user_id', $user_id);
		$res->set('calendar_reservation_start', z::mysqlDatetime($start));
		$res->set('calendar_reservation_cosmetic_service_id', $service_id);
		$res->set('calendar_reservation_duration', $duration);
		$res->set('calendar_reservation_whole_day', $whole_day);

		$res->save();
		return $res;
	}

	function saveReservationJson(int $user_id, object $res): CalendarReservationModel {
		$start = z::parseDatetime($res->start);
		return $this->saveReservation(
			$res->id ?? null,
			$user_id,
			$start,
			$res->cosmetic_service_id ?? 0,
			$res->duration ?? 1,
			$res->whole_day ?? 0
		);
	}

	function onReservationCreated() {
		//send email to customer
		//send email to admin
	}
}
