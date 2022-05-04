<?php

abstract class Si_View_Page extends Si_View {

	abstract public function body ($params=array());
	abstract public function get_title ();
	abstract public function get_step ();

	public function get_step_titles_map () {
		return array(
			Si_Controller_Install::STEP_CHECK => 'Requirements Check',
			Si_Controller_Install::STEP_CONFIG => 'Configuration',
			Si_Controller_Install::STEP_DEPLOY => 'Deployment',
		);
	}

	public function get_state_step ($state) {
		$states_map = Si_Controller_Install::get_states_step_map();

		foreach ($states_map as $step => $states) {
			if (in_array($state, $states)) return $step;
		}

		return false;
	}

	public function get_first_step_state ($step) {
		$states_map = Si_Controller_Install::get_states_step_map();
		if (empty($states_map[$step])) return false;

		$map = $states_map[$step];
		if (!is_array($map)) return false;

		return reset($map);
	}

	public function get_current_state_step () {
		$state = $this->get_state();
		return $this->get_state_step($state);
	}

	public function get_previous_step_titles () {
		$steps = array();
		$all = $this->get_step_titles_map();
		$current_step = $this->get_current_state_step();

		$start = true;
		foreach ($all as $step => $title) {
			if ($step === $current_step) $start = false;
			if ($start) $steps[$this->get_first_step_state($step)] = $title;
		}

		return $steps;
	}

	public function get_next_step_titles () {
		$steps = array();
		$all = $this->get_step_titles_map();
		$current_step = $this->get_current_state_step();

		$start = false;
		foreach ($all as $step => $title) {
			if ($start) $steps[$this->get_first_step_state($step)] = $title;
			if ($step === $current_step) $start = true;
		}

		return $steps;
	}

	public function get_next_steps () {

	}

	public function header ($params=array()) {
		$step_titles = $this->get_step_titles_map();
		$total_steps = count($step_titles);
		$current_position = $this->get_current_state_step();

		$current_step = $this->get_step();
		$current_title = $current_step >= 0 && !empty($step_titles[$current_position])
			? $step_titles[$current_position]
			: $this->get_title()
		;

		$previous_steps = $this->get_previous_step_titles();

		$request = Si_Model_Request::load(Si_Model_Request::REQUEST_EMPTY);
		$styles = new Si_View_Style;
		$img = new Si_View_Img;


		?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">

	<title>Snapshot: <?php echo $this->get_title(); ?></title>
	<meta name="author" content="Incsub">
	<link href='https://fonts.googleapis.com/css?family=Roboto:400,500,700' rel='stylesheet' type='text/css'>
	<link href='https://fonts.googleapis.com/css?family=Roboto+Condensed:400,700' rel='stylesheet' type='text/css'>

	<?php $styles->out(); ?>

</head>
<body>
	<div class="main-header">
		<header>
			<?php $img->out(); ?>
			<h1>Snapshot v4 Migration Wizard</h1>
		</header>
	</div>
	<div class="body">
		<div class="out">
			<h2>
			<?php if ($current_step >= 0) { ?>
				<?php echo $current_step - 1; ?>/<?php echo $total_steps; ?> Steps complete
			<?php } else { ?>
				<?php echo $total_steps; ?>/<?php echo $total_steps; ?> Steps complete
			<?php } ?>
			</h2>
		<?php foreach ($previous_steps as $idx => $ttl) { ?>
			<div class="step empty">
				<div class="step-title">
					<h3>
						<?php
							$args = array('state' => $idx);
							if ('check' === $idx) $args['preview'] = true;
						?>
						<a href="<?php echo $request->get_clean_query($args); ?>">
							<?php echo $ttl; ?>
						</a>
					</h3>
				</div>
				<div class="step-status">
					<div class="success"><span>Passed</span></div>
				</div>
				<div class="step-output"></div>
			</div>
		<?php } ?>
			<div class="step current state-<?php echo $this->get_state(); ?>">
				<div class="step-title">
					<h3><?php echo $current_title; ?></h3>
				</div>
		<?php
	}

	/**
	 * Checks whether the step is to be shown as linked
	 *
	 * @param string $idx Step index
	 * @param array $params View data
	 *
	 * @return bool
	 */
	public function prevents_next_steps ($idx, $params) {
		return 'extract' === $idx;
	}

	public function footer ($params=array()) {
		$next_steps = $this->get_next_step_titles();
		$request = Si_Model_Request::load(Si_Model_Request::REQUEST_EMPTY);

		?>
			</div> <!-- .step -->
		<?php foreach ($next_steps as $idx => $ttl) { ?>
			<div class="step empty">
				<div class="step-title">
					<h3>
					<?php if ($this->prevents_next_steps($idx, $params)) { ?>
						<?php echo $ttl; ?>
					<?php } else { ?>
						<a href="<?php echo $request->get_clean_query('state', $idx); ?>">
							<?php echo $ttl; ?>
						</a>
					<?php } ?>
					</h3>
				</div>
				<div class="step-output"></div>
			</div>
		<?php } ?>
		</div> <!-- .out -->
	</div> <!-- .body -->
</body>
</html>
		<?php
	}

	public function out ($params=array()) {
		$this->header($params);
		$this->body($params);
		$this->footer($params);
	}
}
