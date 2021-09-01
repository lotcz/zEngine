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

}
