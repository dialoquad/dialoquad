<?php

/**
 * This class renders the html for the single event page.
 *
 * @author     Time.ly Network Inc.
 * @since      2.0
 *
 * @package    AI1EC
 * @subpackage AI1EC.View.Event
 */
class Ai1ec_View_Event_Single extends Ai1ec_Base {

	/**
	 * Renders the html of the page and returns it.
	 * 
	 * @param Ai1ec_Event $event
	 * 
	 * @return string the html of the page
	 */
	public function get_content( Ai1ec_Event $event ) {
		$settings = $this->_registry->get( 'model.settings' );
		$rrule    = $this->_registry->get( 'recurrence.rule' );
		$taxonomy = $this->_registry->get( 'view.event.taxonomy' );
		$location = $this->_registry->get( 'view.event.location' );
		$ticket   = $this->_registry->get( 'view.event.ticket' );
		$content  = $this->_registry->get( 'view.event.content' );
		$time     = $this->_registry->get( 'view.event.time' );
		
		$subscribe_url = AI1EC_EXPORT_URL . '&ai1ec_post_ids=' .
			$event->get( 'post_id' );
		$subscribe_url = str_replace( 'webcal://', 'http://', $subscribe_url );
		$event->set_runtime(
			'tickets_url_label',
			$ticket->get_tickets_url_label( $event, false )
		);
		$event->set_runtime(
			'content_img_url',
			$content->get_content_img_url( $event )
		);

		$extra_buttons = apply_filters(
			'ai1ec_rendering_single_event_actions',
			'',
			$event
		);

		$venues_html = apply_filters(
			'ai1ec_rendering_single_event_venues',
			nl2br( $location->get_location( $event ) ),
			$event
		);

		$args = array(
			'event'                   => $event,
			'recurrence'              => $rrule->rrule_to_text( $event->get( 'recurrence_rules' ) ),
			'exclude'                 => $time->get_exclude_html( $event, $rrule ),
			'categories'              => $taxonomy->get_categories_html( $event ),
			'tags'                    => $taxonomy->get_tags_html( $event ),
			'location'                => $venues_html,
			'map'                     => $location->get_map_view( $event ),
			'contact'                 => $ticket->get_contact_html( $event ),
			'back_to_calendar'        => $content->get_back_to_calendar_button_html(),
			'subscribe_url'           => $subscribe_url,
			'edit_instance_url'       => null,
			'edit_instance_text'      => null,
			'google_url'              => 'http://www.google.com/calendar/render?cid=' . urlencode( $subscribe_url ),
			'show_subscribe_buttons'  => ! $settings->get( 'turn_off_subscription_buttons' ),
			'hide_featured_image'     => $settings->get( 'hide_featured_image' ),
			'extra_buttons'           => $extra_buttons
		);

		if (
			! empty( $args['recurrence'] ) &&
			$event->get( 'instance_id' ) &&
			current_user_can( 'edit_ai1ec_events' )
		) {
			$args['edit_instance_url'] = admin_url(
				'post.php?post=' . $event->get( 'post_id' ) .
				'&action=edit&instance=' . $event->get( 'instance_id' )
			);
			$args['edit_instance_text'] = sprintf(
				Ai1ec_I18n::__( 'Edit this occurrence (%s)' ),
				$event->get( 'start' )->format_i18n( 'M j' )
			);
		}
		$loader = $this->_registry->get( 'theme.loader' );
		return $loader->get_file( 'event-single.twig', $args, false )
			->get_content();
	}

	/**
	 * @param Ai1ec_Event $event
	 * 
	 * @return The html of the footer
	 */
	public function get_footer( Ai1ec_Event $event ) {
		$loader = $this->_registry->get( 'theme.loader' );
		$args   = array(
			'event' => $event,
		);
		return $loader->get_file( 'event-single-footer.twig', $args, false )
			->get_content();
	}

}