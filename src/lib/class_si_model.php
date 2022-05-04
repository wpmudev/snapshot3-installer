<?php

abstract class Si_Model {

	/**
	 * Deep-trims value
	 *
	 * @param mixed $value Value to deep-trim
	 *
	 * @return mixed
	 */
	public function deep_trim ($value) {
		if (!is_array($value)) {
			if (is_numeric($value) && !strstr($value, '.')) $value = (int)$value;
			else $value = trim($value);

			return $value;
		}
		foreach ($value as $key => $val) {
			$value[$key] = $this->deep_trim($val);
		}
		return $value;
	}
}
