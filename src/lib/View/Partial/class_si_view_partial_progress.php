<?php

class Si_View_Partial_Progress extends Si_View {

	public function out ($params=array()) {
		$percentage = !empty($params['percentage']) && is_numeric($params['percentage'])
			? (int)$params['percentage']
			: 30
		;
		$action = !empty($params['action'])
			? $params['action']
			: 'Deploying package'
		;
		?>
		<div class="progress">
			<div class="progress-bar_wrapper">
				<div class="progress-bar">
					<div class="progress-bar_indicator <?php if ($percentage < 28) echo 'percentage-only'; ?>" style="width: <?php echo(int)$percentage; ?>%">
						<span class="progress-bar_message">Deployment in progress... </span>
						<span class="progress-bar_percentage"><?php echo (int)$percentage; ?>%</span>
					</div>
				</div>
				<div class="progress-info">
					<p><?php echo $action; ?></p>
				</div>
			</div>
		</div>
		<?php
		return false;
	}
}
