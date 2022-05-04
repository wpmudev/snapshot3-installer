<?php

class Si_Controller_Install extends Si_Controller {

	const STEP_CHECK = 0;
	const STEP_CONFIG = 1;
	const STEP_DEPLOY = 2;

	public function run () {
		?>
		<h1>Heh, something went wrong routing your request o.0</h1>
		<?php
	}

	public static function get_states_step_map () {
		return array(
			self::STEP_CHECK => array('check'),
			self::STEP_CONFIG => array('configuration'),
			self::STEP_DEPLOY => array('extract', 'files', 'tables', 'finalize', 'done')
		);
	}

	public function route () {
		$controller = $this;
		$state = $this->_request->get('state', 'check');

		@set_time_limit(0);

		switch ($state) {
			case 'extract':
				$controller = new Si_Controller_Extract;
				break;
			case 'files':
				$controller = new Si_Controller_Files;
				break;
			case 'tables':
				$controller = new Si_Controller_Tables;
				break;
			case 'finalize':
				$controller = new Si_Controller_Finalize;
				break;
			case 'done':
				$controller = new Si_Controller_Done;
				break;
			case 'cleanup':
				$controller = new Si_Controller_Cleanup;
				break;
			case 'configuration':
				$controller = new Si_Controller_Configuration;
				break;
			case 'check':
			default:
				$controller = new Si_Controller_Check;
				$state = 'check';
				break;
		}

		$controller->set_view_class($state);
		$controller->run();
	}

}
