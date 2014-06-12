<?php

/**
 * Renderer of settings page html.
 *
 * @author       Time.ly Network, Inc.
 * @instantiator new
 * @since        2.0
 * @package      Ai1EC
 * @subpackage   Ai1EC.Html
 */
class Ai1ec_Html_Setting_Html extends Ai1ec_Html_Element_Settings {

	/* (non-PHPdoc)
	 * @see Ai1ec_Html_Element_Settings::render()
	 */
	public function render( $output = '' ) {
		$file   = $this->_args['id'] . '.twig';
		$method = 'get_' . $this->_args['id'] . '_args';
		$args   = array();
		if ( method_exists( $this, $method ) ) {
			$args = $this->{$method}();
		}
		$loader = $this->_registry->get( 'theme.loader' );
		$file   = $loader->get_file( 'setting/' . $file, $args, true );
		return parent::render( $file->get_content() );
	}

	/*
	 * Get embedding arguments
	 *
	 * @return array
	 */
	protected function get_embedding_args() {
		return array(
			'viewing_events_shortcodes' => apply_filters( 'ai1ec_viewing_events_shortcodes', null ),
		);
	}

}