<?php

class Si_Controller_Done extends Si_Controller {

	public function run () {
		$this->get_view()->out(array(
			'status' => true,
			'cleanup_url' => $this->_request->get_query('state', 'cleanup'),
			'view_url' => $this->_env->get(Si_Model_Env::TARGET_URL),
		));
	}
}
