<?php

class Si_View_Page_Check extends Si_View_Page {

	public function get_title () {
		return 'Requirements check';
	}

	public function get_step () { return 1; }

	/**
	 * Check if we're able to link up the next step
	 *
	 * If we don't have an archive present, we are not
	 *
	 * {@inheritDoc}
	 */
	public function prevents_next_steps ($idx, $params) {
		if (parent::prevents_next_steps($idx, $params)) return true;

		if (empty($params['checks'])) return true; // No checks, not proper data
		return empty($params['checks']['Archive']['test']);
	}


	public function body ($params=array()) {
		$checks = !empty($params['checks']) && is_array($params['checks'])
			? $params['checks']
			: array()
		;
		$overall = true; $checks = array();

		foreach ($params['checks'] as $cname => $check) {
			$class = 'Si_View_Partial_' . $cname;
			if (!class_exists($class)) continue;

			if (empty($check['test'])) $overall = false;
			$checks[$cname] = new $class;
		}
		?>
	<div class="step-status">
	<?php if ($overall) { ?>
		<div class="success"><span>Passed</span></div>
	<?php } else { ?>
		<div class="failed"><span>Failed</span></div>
	<?php } ?>
	</div>

	<div class="step-output">
		<?php
		foreach ($checks as $cname => $check) {
			$check->out($params['checks'][$cname]);
		}
		?>
	</div> <!-- .step-output -->
</div> <!-- .step -->
		<?php

		return $overall;
	}
}
