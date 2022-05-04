<?php

abstract class Si_Controller {

	abstract public function run ();

	protected $_request;
	protected $_env;
	protected $_view_class;

	private $_view;

	public function __construct () {
		$this->_request = Si_Model_Request::load(Si_Model_Request::REQUEST_GET);
		$this->_env = new Si_Model_Env;
	}

	/**
	 * Sets view class to use for this controller instance
	 *
	 * @param string $type View class type
	 */
	public function set_view_class ($type) {
		$this->_view_class = $type;
	}

	/**
	 * Gets view instance
	 *
	 * @return object An appropriate Si_View_Page instance
	 */
	public function get_view () {
		if (!empty($this->_view)) return $this->_view;

		$cls = 'Si_View_Page_' . ucfirst($this->_view_class);
		if (!class_exists($cls)) {
			$cls = 'Si_View_Page_Error';
		}
		$this->_view = new $cls;

		$this->_view->set_state($this->_view_class);

		return $this->_view;
	}

	/**
	 * Re-routes (redirects) according to the request query vars
	 */
	public function reroute () {
		$url = self::get_base_url();
		$query = $this->_request->to_query();
		header("Location: {$url}{$query}");
		die;
	}

	/**
	 * Gets the absolute current URL
	 *
	 * @return string
	 */
	public static function get_base_url () {
		$protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on'
			? 'https'
			: 'http'
		;
		$host = !empty($_SERVER['HTTP_HOST'])
			? rtrim($_SERVER['HTTP_HOST'], '/')
			: ''
		;
		$uri = !empty($_SERVER['PHP_SELF'])
			? ltrim($_SERVER['PHP_SELF'], '/')
			: ''
		;
		return "{$protocol}://{$host}/{$uri}";
	}

	/**
	 * Gets full URL to relative path
	 *
	 * @param string $relative_path Relative path
	 *
	 * @return string
	 */
	public static function get_url ($relative_path) {
		$target = rtrim(dirname(self::get_base_url()), '/');
		if (preg_match('/\/(src|build)/', $target)) {
			$target = rtrim(dirname($target), '/');
		}
		return $target . '/' . ltrim($relative_path, '/');
	}
}
