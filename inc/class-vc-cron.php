<?php
defined( 'ABSPATH' ) || exit;

class VC_Cron {

	public function __construct() {

	}

	/**
	 * Setup actions and filters
	 *
	 * @since 1.0
	 */
	private function setup() {

		add_action( 'vc_purge_cache', array( $this, 'purge_cache' ) );
		add_action( 'init', array( $this, 'schedule_events' ) );
		add_filter( 'cron_schedules', array( $this, 'filter_cron_schedules' ) );
	}

	/**
	 * Add custom cron schedule
	 *
	 * @param  array $schedules
	 * @since  1.0
	 * @return array
	 */
	public function filter_cron_schedules( $schedules ) {

		$config = VC_Config::factory()->get();

		$interval = HOUR_IN_SECONDS;

		if ( ! empty( $config['page_cache_length'] ) && $config['page_cache_length'] > 0 ) {
			$interval = $config['page_cache_length'] * 60;
		}

		$schedules['voyar_cache'] = array(
			'interval' => apply_filters( 'vc_cache_purge_interval', $interval ),
			'display'  => esc_html__( 'Voyar Cache Önbellek Temizleme Aralığı', 'voyar-cache' ),
		);
		return $schedules;
	}

	/**
	 * Unschedule events
	 *
	 * @since  1.4.1
	 */
	public function unschedule_events() {
		$timestamp = wp_next_scheduled( 'vc_purge_cache' );

		wp_unschedule_event( $timestamp, 'vc_purge_cache' );
	}

	/**
	 * Setup cron jobs
	 *
	 * @since 1.0
	 */
	public function schedule_events() {

		$config = VC_Config::factory()->get();

		$timestamp = wp_next_scheduled( 'vc_purge_cache' );

		// Do nothing if we are using the object cache
		if ( ! empty( $config['advanced_mode'] ) && ! empty( $config['enable_in_memory_object_caching'] ) ) {
			wp_unschedule_event( $timestamp, 'vc_purge_cache' );
			return;
		}

		// Expire cache never
		if ( isset( $config['page_cache_length'] ) && $config['page_cache_length'] === 0 ) {
			wp_unschedule_event( $timestamp, 'vc_purge_cache' );
			return;
		}

		if ( ! $timestamp ) {
			wp_schedule_event( time(), 'voyar_cache', 'vc_purge_cache' );
		}
	}

	/**
	 * Initiate a cache purge
	 *
	 * @since 1.0
	 */
	public function purge_cache() {
		$config = VC_Config::factory()->get();

		// Do nothing, caching is turned off
		if ( empty( $config['enable_page_caching'] ) ) {
			return;
		}

		// Do nothing if we are using the object cache
		if ( ! empty( $config['advanced_mode'] ) && ! empty( $config['enable_in_memory_object_caching'] ) ) {
			return;
		}

		vc_cache_flush();
	}

	/**
	 * Return an instance of the current class, create one if it doesn't exist
	 *
	 * @since  1.0
	 * @return object
	 */
	public static function factory() {

		static $instance;

		if ( ! $instance ) {
			$instance = new self();
			$instance->setup();
		}

		return $instance;
	}
}
