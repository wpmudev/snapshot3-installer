<?php

class Si_View_Page_Error extends Si_View_Page {

	public function get_title () {
		return 'Uh oh, something went wrong!';
	}
	public function get_step () { return -1; }

	public function body ($params=array()) {

	}
}
