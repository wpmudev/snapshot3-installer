<?php

class Si_View_Style extends Si_View {

	public function out ($params=array()) {
		?>
		<style>
		*, *:before, *:after {
			box-sizing: border-box;
		}
		body {
			background: #F4F4F4;
			color: #555555;
			font-family: "Roboto", sans-serif;
			margin: 0;
			padding: 0;
		}

		.main-header h1 {
			text-align: center;
			text-transform: uppercase;
			margin: 0;
			padding: 0;
			margin-top: 20px;
			padding-left: 150px;
			line-height: 100px;
			font-size: 50px;
			font-family: "Roboto Condensed";
			letter-spacing: -2px;
		}
		.main-header svg {
			position: absolute;
			top: 0;
			left: 30px;
		}
		.main-header header {
			position: relative;
			height: 120px;
		}

		.body, .main-header header {
			width: 1000px;
			margin: 0 auto;
		}

		.body {
			background: #ffffff;
		}
		body h2 {
			padding: 18px 30px;
			border-bottom: 1px solid #EEEEEE;
			text-transform: uppercase;
			font-size: 18px;
			font-family: "Roboto Condensed";
			margin: 0;
		}
		.step {
			padding: 18px 30px;
			border-bottom: 1px solid #EEEEEE;
		}

		.step-title, .step-status {
			display: inline-block;
			float: left;
			line-height: 2em;
		}
		.step-title h3 {
			display: inline;
			margin: 0;
			padding: 0;
			font-size: 14px;
		}
		.step-title h3 a {
			text-decoration: none;
			color: #555555;
		}
		.step-title {
			margin-bottom: 18px;
		}
		.step-output {
			clear: both;
			padding: 20px 30px;
			border-radius: 3px;
		}
		.empty .step-title { margin: 0; }
		.empty .step-output { padding: 0; }


		.step-status {
			color: #fff;
			text-align: center;
			text-transform: uppercase;
			width: 100px;
			margin-left: 30px;
			font-size: 13px;
		}
		.step-status div { border-radius: 3px; }
		.step-status span { display: inline-block; padding: 8px 10px; line-height: 1em;}
		.step-status .success { background: #1ABC9C; }
		.step-status .failed { background: #FF6D6D; }
		.step-status .warning { background: #FECF2F; }
		.step-output { background: #F9F9F9; }

		button, a.button {
			background: #A9A9A9;
			color: #FFFFFF;
			text-decoration: none;
			text-transform: uppercase;
			border: none;
			padding: 10px 30px;
			font-size: 1em;
			border-radius: 4px;
			font-weight: 500;
			font-family: "Roboto";
			border-bottom: 3px solid #A9A9A9;
		}
		button.primary, a.button.primary {
			background: #19B4CF;
			color: #FFFFFF;
			border-bottom: 3px solid #1490A5;
		}
		</style>
		<?php
		$this->_check();
		$this->_configuration();
		$this->_deployment();
	}

	private function _check () {
		?>
		<style>
		/**
		 * --- Check step ---
		 */
		 .check {
		 	padding: 15px 0;
		 	border-bottom: 1px solid #EEEEEE;
		 }
		 .check:first-child {
			 padding-top: 5px;
		 }
		 .check:last-child {
		 	border: none;
		 }
		 .check .check-title {
		 	display: table-cell;
		 	width: 180px;
		 }
		 .check .check-status {
		 	display: table-cell;
		 	width: 100px;
		 	color: #FFFFFF;
		 	text-align: center;
		 	text-transform: uppercase;
			font-size: 13px;
		 }
		 .check .check-output {
		 	display: table-cell;
		 	padding-left: 20px;
		 }

		 .check .check-title h4 {
			 margin: 0;
			 padding: 0;
			 font-size: 15px;
			 font-weight: 500;
		 }
		 .check .check-status div { border-radius: 3px; }
		 .check-status span { display: inline-block; padding: 8px 10px; }
		 .check .success { background: #1ABC9C; }
		 .check .failed { background: #FF6D6D; }
		 .check .warning { background: #FECF2F; }
		</style>
		<?php
	}

	private function _configuration () {
		?>
		<style>
		/**
		 * --- Configuration step ---
		 */
		.state-configuration {
			font-size: 15px;
			line-height: 1.4em;
		}
		.state-configuration .error-message {
			background: #FF6D6D;
			color: #FFFFFF;
			padding: 10px;
			border-radius: 5px;
		}
		.state-configuration .step-output h3 {
			text-transform: uppercase;
			margin: 20px 0;
			margin-top: 30px;
			font-size: 18px;
			font-family: "Roboto Condensed";
		}
		form input {
			border: 1px solid #EEEEEE;
			padding: 10px;
			background: #FFFFFF;
			color: #2A6988;
			font-weight: bold;
		}
		.config-item {
			margin: 10px 0;
		}
		.config-item label span {
			display: inline-block;
			width: 200px;
			font-weight: 500;
		}
		.config-item input {
			width: 450px;
			border-radius: 3px;
			font-size: 15px;
		}

		.config-item input.error { border: 1px solid #f33; }
		.config-item input.warning { border: 1px solid #FECF2F; }

		.config-item.host input { width: 275px; }
		.config-item.host label[for="port"] span { padding-left: 25px; width: 65px; }
		.config-item.host label[for="port"] input { width: 100px; }

		.config-test {
			background: #EBFCFF;
			color: #487386;
			border: 1px solid #B5D3E0;
			padding: 0 20px;
			border-radius: 5px;
			margin: 20px 0;
		}
		.config-test .result-item {
			padding: 15px 0;
			border-bottom: 1px solid #B5D3E0;
		}
		.config-test .result-item:last-child {
			border: none;
		}
		.config-test .check-title {
			display: table-cell;
			width: 200px;
		}
		.config-test .check-status {
			display: table-cell;
			width: 100px;
			color: #FFFFFF;
			text-align: center;
			text-transform: uppercase;
		}
		.config-test .check-output {
			display: table-cell;
			padding-left: 20px;
		}
		.config-test .check-status div { border-radius: 3px; font-size: 13px; }
		.config-test .check-status div span { padding: 4px 5px; }
		.config-test .result-item .success { background: #1ABC9C; }
		.config-test .result-item .failed { background: #FF6D6D; }
		.config-test .result-item .warning { background: #FECF2F; }
		.config-test .result-item .stopped { background: #EAEAEA; color: #8B8B8B; }

		.config-actions {
			margin: 20px 0;
		}

		.continue p {
			display: inline-block;
		}
		.continue p:first-child {
			width: 80%;
		}
		.continue p:last-child {
			float: right;
		}
		</style>
		<?php
	}

	private function _deployment () {
		?>
		<style>
		/**
		* --- Deployment step ---
		*/
		.deployment header {
			text-align: center;
		}
		.deployment header h3 {
			text-transform: uppercase;
			font-family: "Roboto Condensed";
			font-weight: normal;
			font-size: 24px;
			margin-bottom: 15px;
		}
		</style>
		<?php
		$this->_deployment_failure();
		$this->_deployment_success();
		$this->_progress_bar();
		$this->_cleanup();
	}

	private function _deployment_failure () {
		?>
		<style>
		/**
		* Failure
		*/
		.deployment.failure {
			text-align: center;
		}
		.deployment.failure .error {
			background: #FF6D6D;
			color: #FFFFFF;
			padding: 10px;
			border-radius: 5px;
		}
		</style>
		<?php
	}

	private function _deployment_success () {
		?>
		<style>
		/**
		* Success
		*/
		.deployment.success {
			text-align: center;
		}
		.deployment.success .success {
			background: #1ABC9C;
			color: #FFFFFF;
			padding: 10px;
			border-radius: 5px;
		}
		.deployment.actions {
			text-align: center;
		}
		.deployment.actions p {
			display: inline-block;
		}
		</style>
		<?php
	}

	private function _progress_bar () {
		?>
		<style>
		.progress .progress-bar_wrapper {
		}
		.progress .progress-bar {
			background: #14485F;
			border-radius: 5px;
			padding: 5px;
			height: 50px;
		}
		.progress .progress-bar_indicator {
			background: #FECF2F;
			color: #14485F;
			border-radius: 5px;
			white-space: nowrap;
			line-height: 50px;
		}
		.progress .progress-bar_indicator span {
			padding: 0 10px;
		}
		.progress .progress-bar_indicator.percentage-only span.progress-bar_message {
			display: none;
		}
		.progress .progress-info {
			text-align: center;
			font-size: 12px;
		}
		/**
		* Progress bar color and animation
		*/
		.progress .progress-bar_indicator {
			background-image: linear-gradient(135deg,rgba(255,255,255,.4) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.4) 50%,rgba(255,255,255,.4) 75%,transparent 75%,transparent);
			background-color: #FECF2F;
			background-size: 38px 38px;
			border-top-left-radius: 5px;
			border-bottom-left-radius: 5px;
			box-shadow: 0 1px 1px rgba(0,0,0,.75);
			height: 40px;
			line-height: 40px;
			min-width: 5%;
			animation: si-animate-progress-bars 2s linear infinite;
		}
		@keyframes si-animate-progress-bars {
			from  { background-position: 38px 0; }
			to    { background-position: 0 0; }
		}
		</style>
		<?php
	}

	private function _cleanup () {
		?>
		<style>
		/**
		* Success
		*/
		.cleanup.success {
			text-align: center;
		}
		.cleanup.success div, .cleanup.warning div {
			color: #FFFFFF;
			padding: 10px;
			border-radius: 5px;
			text-align: center;
		}
		.cleanup.actions {
			text-align: center;
		}
		.cleanup.actions p {
			display: inline-block;
		}

		.cleanup-status {
			text-transform: uppercase;
		}
		.cleanup-status h3 {
			display: inline-block;
			font-family: "Roboto Condensed";
		}
		.cleanup-status div {
			display: inline-block;
			margin-left: 30px;
			width: 100px;
			border-radius: 3px;
			font-size: 13px;
			padding: 4px 5px;
			text-align: center;
		}
		.cleanup-status .success { background: #1ABC9C; color: #FFFFFF; }
		.cleanup-status .warning { background: #FECF2F; color: #FFFFFF; }

		.cleanup.success .success { background: #1ABC9C; }
		.cleanup.warning .warning { background: #FECF2F; }

		.cleanup-results-root {
			background: #EBFCFF;
			color: #487386;
			border: 1px solid #B5D3E0;
			padding: 0 20px;
			border-radius: 5px;
			margin: 20px 0;
		}
		.cleanup-results-root .result-item {
			padding: 15px 0;
			border-bottom: 1px solid #B5D3E0;
		}
		.cleanup-results-root .result-item:last-child {
			border: none;
		}
		.cleanup-results-root .result-item-title {
			display: table-cell;
			padding-right: 20px;
			width: 80%;
		}
		.cleanup-results-root .result-item-status {
			display: table-cell;
			width: 100px;
			min-width: 100px;
			color: #FFFFFF;
			text-align: center;
			text-transform: uppercase;
		}
		.cleanup-results-root .result-item-status div { border-radius: 3px; font-size: 13px; }
		.cleanup-results-root .result-item-status div span { display: inline-block; padding: 8px 10px; }
		.cleanup-results-root .result-item .success { background: #1ABC9C; }
		.cleanup-results-root .result-item .failed { background: #FF6D6D; }
		.cleanup-results-root .result-item .warning { background: #FECF2F; }
		</style>
		<?php
	}
}
