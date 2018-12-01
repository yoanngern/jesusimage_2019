<?php


/**
 * Go_Live_Update_URLS_Pro_Tests_Abstract
 *
 * @author Mat Lipe
 * @since  2.0.0
 *
 */
abstract class Go_Live_Update_URLS_Pro_Tests_Abstract implements JsonSerializable {
	protected $old_url;
	protected $new_url;

	protected $result;

	abstract protected function test();

	abstract protected function get_message();

	abstract public function get_fixed();


	/**
	 * @internal
	 * @todo find a way to unit this on PHP 5.2 WHERE call_private_method()
	 *       does not work yet.
	 *
	 * @return string
	 */
	public function get_message_for_unit_tests(){
		return $this->get_message();
	}
}