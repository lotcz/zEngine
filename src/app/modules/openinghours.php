<?php

define("OPEN_HOURS_WEEKDAYS", array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday"));
define("OPEN_HOURS_WEEKDAYS_SHORT", array("Po", "Út", "St", "Čt", "Pá", "So", "Ne"));
define("OPEN_HOURS_WEEKDAYS_CZECH", array("Pondělí", "Úterý", "Středa", "Čtvrtek", "Pátek", "Sobota", "Neděle"));

/**
* Module that handles opening hours form field.
*/
class openinghoursModule extends zModule {

	/**
	 * Return true if hours are set (00:00:00 is considered an empty value)
	 * @param  string  $value
	 * @return boolean       Return true if opening hours are defined
	 */
	function isset($value) {
		return ((isset($value)) && ($value != ''));
	}

	/**
	 * transforms index from  PHP 0 - 6 (sunday to saturday) to classic 0 - 6 (monday to sunday)
	 * @param  [type] $i [description]
	 * @return [type]    [description]
	 */
	function transformIndex($i) {
		$new_index = $i - 1;
		while ($new_index < 0) {
			$new_index += 7;
		}
		return $new_index % 7;
	}

	/**
	 * return name of weekday based on day number: 0 - sunday, 6 - saturday
	 * @param  [type] $i [description]
	 * @return [type]    [description]
	 */
	function getDayName($i) {
		return OPEN_HOURS_WEEKDAYS[$this->transformIndex($i)];
	}

	/**
	 * formats opening time for display
	 * @param  [type] $value [description]
	 * @return [type]    [description]
	 */
	function formatTime($value) {
		if ($this->isset($value)) {
			return substr($value, 0, 5);
		} else {
			return '';
		}
	}

	/**
	 * return name of weekday in czech based on day number: 0 - sunday, 6 - saturday
	 * @param  [type] $i [description]
	 * @return [type]    [description]
	 */
	function getDayNameCzech($i) {
		return OPEN_HOURS_WEEKDAYS_CZECH[$this->transformIndex($i)];
	}

	/**
	 * return abbreviated name of weekday based on day number: 0 - sunday, 6 - saturday
	 * @param  [type] $i [description]
	 * @return [type]    [description]
	 */
	function getDayNameShort($i) {
		return OPEN_HOURS_WEEKDAYS_SHORT[$this->transformIndex($i)];
	}

	function getTime($data, $key) {
		return isset($data[$key]) && $data[$key] != '' ? $data[$key] : null;
	}

	function renderFormField($field) {

		?>
			<label class="control-label form-label"><?=$this->z->core->t($field->label) ?>:</label>
		<?php

		for ($d = 1; $d <= 7; $d++) {
			$day_name = $this->getDayName($d);
			$from = $field->prefix . $day_name . '_from';
			$to = $field->prefix . $day_name . '_to';
			?>
				<div class="d-flex form-group">
					<div class="form-field">
						<label class="control-label col-form-label pr-1"><strong><?=$this->getDayNameShort($d) ?></strong></label>
					</div>
					<div class="form-field">
						<input type="text" name="<?=$from ?>" placeholder="HH:MM" value="<?=$this->formatTime($field->value[$from])?>" class="form-control"/>
					</div>
					<div class="form-field">
						<label class="control-label col-form-label px-1"><span class="sep">–</span></label>
					</div>
					<div>
						<input type="text" name="<?=$to ?>" placeholder="HH:MM" value="<?=$this->formatTime($field->value[$to])?>" class="form-control" />
					</div>
				</div>
			<?php
		}
	}

	function company_hasOpeningHoursForDay($company, $day) {
		$day_name = $this->getDayName($day);
		return $this->isset($company->val(sprintf('company_%s_from', $day_name))) && $this->isset($company->val(sprintf('company_%s_to', $day_name)));
	}

	/**
	 * Return true if property has opening hours defined
	 * @param  [type]  $item database record from pgm_custom_companies
	 * @return boolean       Return true if opening hours are defined for at least one day of a week.
	 */
	function company_hasOpeningHours($company) {
		for ($i = 0; $i < 7; $i++) {
			if ($this->company_hasOpeningHoursForDay($company, $i)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Return true if property is open right now.
	 * @param  [type]  $item database record from pgm_custom_companies
	 * @return boolean       If opening hours are not defined, return null. Otherwise use current date and time to decide whether it is open or not.
	 */
	function company_isOpen($company) {
		$current_day = intval(date('w'));
		$day_name = $this->getDayName($current_day);
		$current_time = date('H:i:s');
		$today_opens = $company->val('company_' . $day_name . '_from');
		$today_closes = $company->val('company_' . $day_name . '_to');

		/* is open in today's standard hours? */
		$open_today = ($this->company_hasOpeningHoursForDay($company, $current_day) && $today_opens < $current_time) && (($today_closes > $current_time) || ($today_opens > $today_closes));

		if ($open_today) {
			return true;
		} else if ($this->company_hasOpeningHoursForDay($company, $current_day - 1)) {
			$yesterday_name = $this->getDayName($current_day - 1);
			$yesterday_opens = $company->val('company_' . $yesterday_name . '_from');
			$yesterday_closes = $company->val('company_' . $yesterday_name . '_to');
			/* is still open since yesterday? (night bars etc...) */
			return ($yesterday_opens > $yesterday_closes) && ($current_time < $yesterday_closes);
		}
	}

}
