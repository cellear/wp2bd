<?php
/**
 * WordPress Cron API
 *
 * @package WordPress
 */

/**
 * Schedules an event to run only once.
 *
 * Schedules an event which will execute once by the WordPress actions core at
 * a time which you specify. The action will fire off when someone visits your
 * WordPress site, if the schedule time has passed.
 *
 * Note that scheduling an event to occur within 10 minutes of an existing event
 * with the same action hook will be ignored unless you pass unique `$args` values
 * for each scheduled event.
 *
 * @since 2.1.0
 * @link https://codex.wordpress.org/Function_Reference/wp_schedule_single_event
 *
 * @param int $timestamp Unix timestamp (UTC) for when to run the event.
 * @param string $hook Action hook to execute when event is run.
 * @param array $args Optional. Arguments to pass to the hook's callback function.
 * @return false|void False if the event does not get scheduled.
 */
function wp_schedule_single_event( $timestamp, $hook, $args = array()) {
	// WP4BD V2-041 (2025-12-16): Cron scheduling disabled
	// WordPress should not schedule background tasks in WP4BD architecture.
	// Backdrop handles all background processing.
	//
	// Original code modified WordPress options table to schedule tasks.
	// In WP4BD, WordPress is read-only and cannot schedule events.

	return false;
}

/**
 * Schedule a recurring event.
 *
 * Schedules a hook which will be executed by the WordPress actions core on a
 * specific interval, specified by you. The action will trigger when someone
 * visits your WordPress site, if the scheduled time has passed.
 *
 * Valid values for the recurrence are hourly, daily, and twicedaily. These can
 * be extended using the {@see 'cron_schedules'} filter in wp_get_schedules().
 *
 * Use wp_next_scheduled() to prevent duplicates
 *
 * @since 2.1.0
 *
 * @param int $timestamp Unix timestamp (UTC) for when to run the event.
 * @param string $recurrence How often the event should recur.
 * @param string $hook Action hook to execute when event is run.
 * @param array $args Optional. Arguments to pass to the hook's callback function.
 * @return false|void False if the event does not get scheduled.
 */
function wp_schedule_event( $timestamp, $recurrence, $hook, $args = array()) {
	// WP4BD V2-041 (2025-12-16): Recurring cron scheduling disabled
	// Original code modified WordPress options to store cron schedules.
	// WordPress is read-only in WP4BD.

	return false;
}

/**
 * Reschedule a recurring event.
 *
 * @since 2.1.0
 *
 * @param int $timestamp Unix timestamp (UTC) for when to run the event.
 * @param string $recurrence How often the event should recur.
 * @param string $hook Action hook to execute when event is run.
 * @param array $args Optional. Arguments to pass to the hook's callback function.
 * @return false|void False if the event does not get rescheduled.
 */
function wp_reschedule_event( $timestamp, $recurrence, $hook, $args = array() ) {
	// WP4BD V2-041 (2025-12-16): Cron rescheduling disabled
	return false;
}

/**
 * Unschedule a previously scheduled event.
 *
 * The $timestamp and $hook parameters are required so that the event can be
 * identified.
 *
 * @since 2.1.0
 *
 * @param int $timestamp Unix timestamp (UTC) for when to run the event.
 * @param string $hook Action hook, the execution of which will be unscheduled.
 * @param array $args Arguments to pass to the hook's callback function.
 * Although not passed to a callback function, these arguments are used
 * to uniquely identify the scheduled event, so they should be the same
 * as those used when originally scheduling the event.
 * @return false|void False if the event does not get unscheduled.
 */
function wp_unschedule_event( $timestamp, $hook, $args = array() ) {
	// WP4BD V2-041 (2025-12-16): Cron unscheduling disabled
	return false;
}

/**
 * Unschedules all events attached to the hook with the specified arguments.
 *
 * @since 2.1.0
 *
 * @param string $hook Action hook, the execution of which will be unscheduled.
 * @param array $args Optional. Arguments that were to be passed to the hook's callback function.
 */
function wp_clear_scheduled_hook( $hook, $args = array() ) {
	// Backward compatibility
	// Previously this function took the arguments as discrete vars rather than an array like the rest of the API
	if ( !is_array($args) ) {
		_deprecated_argument( __FUNCTION__, '3.0.0', __('This argument has changed to an array to match the behavior of the other cron functions.') );
		$args = array_slice( func_get_args(), 1 );
	}

	// This logic duplicates wp_next_scheduled()
	// It's required due to a scenario where wp_unschedule_event() fails due to update_option() failing,
	// and, wp_next_scheduled() returns the same schedule in an infinite loop.
	$crons = _get_cron_array();
	if ( empty( $crons ) )
		return;

	$key = md5( serialize( $args ) );
	foreach ( $crons as $timestamp => $cron ) {
		if ( isset( $cron[ $hook ][ $key ] ) ) {
			wp_unschedule_event( $timestamp, $hook, $args );
		}
	}
}

/**
 * Unschedules all events attached to the hook.
 *
 * Can be useful for plugins when deactivating to clean up the cron queue.
 *
 * @since 4.9.0
 *
 * @param string $hook Action hook, the execution of which will be unscheduled.
 */
function wp_unschedule_hook( $hook ) {
	$crons = _get_cron_array();

	foreach( $crons as $timestamp => $args ) {
		unset( $crons[ $timestamp ][ $hook ] );

		if ( empty( $crons[ $timestamp ] ) ) {
			unset( $crons[ $timestamp ] );
		}
	}

	_set_cron_array( $crons );
}

/**
 * Retrieve the next timestamp for an event.
 *
 * @since 2.1.0
 *
 * @param string $hook Action hook to execute when event is run.
 * @param array $args Optional. Arguments to pass to the hook's callback function.
 * @return false|int The Unix timestamp of the next time the scheduled event will occur.
 */
function wp_next_scheduled( $hook, $args = array() ) {
	$crons = _get_cron_array();
	$key = md5(serialize($args));
	if ( empty($crons) )
		return false;
	foreach ( $crons as $timestamp => $cron ) {
		if ( isset( $cron[$hook][$key] ) )
			return $timestamp;
	}
	return false;
}

/**
 * Sends a request to run cron through HTTP request that doesn't halt page loading.
 *
 * @since 2.1.0
 *
 * @param int $gmt_time Optional. Unix timestamp (UTC). Default 0 (current time is used).
 */
function spawn_cron( $gmt_time = 0 ) {
	// WP4BD V2-041 (2025-12-16): spawn_cron() CRITICAL DISABLE
	// This function makes an HTTP request back to the site itself (wp_remote_post)
	// to trigger background cron processing. This MUST be disabled in WP4BD.
	// All background processing should be handled by Backdrop's cron system.
	//
	// Original function made HTTP POST request to site_url('wp-cron.php')

	return;
}

/**
 * Run scheduled callbacks or spawn cron for all scheduled events.
 *
 * @since 2.1.0
 */
function wp_cron() {
	// WP4BD V2-041 (2025-12-16): Cron execution disabled
	// This function executes scheduled WordPress cron tasks.
	// Disabled in WP4BD - Backdrop handles background processing.

	return;
}

/**
 * Retrieve supported event recurrence schedules.
 *
 * The default supported recurrences are 'hourly', 'twicedaily', and 'daily'. A plugin may
 * add more by hooking into the {@see 'cron_schedules'} filter. The filter accepts an array
 * of arrays. The outer array has a key that is the name of the schedule or for
 * example 'weekly'. The value is an array with two keys, one is 'interval' and
 * the other is 'display'.
 *
 * The 'interval' is a number in seconds of when the cron job should run. So for
 * 'hourly', the time is 3600 or 60*60. For weekly, the value would be
 * 60*60*24*7 or 604800. The value of 'interval' would then be 604800.
 *
 * The 'display' is the description. For the 'weekly' key, the 'display' would
 * be `__( 'Once Weekly' )`.
 *
 * For your plugin, you will be passed an array. you can easily add your
 * schedule by doing the following.
 *
 *     // Filter parameter variable name is 'array'.
 *     $array['weekly'] = array(
 *         'interval' => 604800,
 *     	   'display'  => __( 'Once Weekly' )
 *     );
 *
 *
 * @since 2.1.0
 *
 * @return array
 */
function wp_get_schedules() {
	$schedules = array(
		'hourly'     => array( 'interval' => HOUR_IN_SECONDS,      'display' => __( 'Once Hourly' ) ),
		'twicedaily' => array( 'interval' => 12 * HOUR_IN_SECONDS, 'display' => __( 'Twice Daily' ) ),
		'daily'      => array( 'interval' => DAY_IN_SECONDS,       'display' => __( 'Once Daily' ) ),
	);
	/**
	 * Filters the non-default cron schedules.
	 *
	 * @since 2.1.0
	 *
	 * @param array $new_schedules An array of non-default cron schedules. Default empty.
	 */
	return array_merge( apply_filters( 'cron_schedules', array() ), $schedules );
}

/**
 * Retrieve the recurrence schedule for an event.
 *
 * @see wp_get_schedules() for available schedules.
 *
 * @since 2.1.0
 *
 * @param string $hook Action hook to identify the event.
 * @param array $args Optional. Arguments passed to the event's callback function.
 * @return string|false False, if no schedule. Schedule name on success.
 */
function wp_get_schedule($hook, $args = array()) {
	$crons = _get_cron_array();
	$key = md5(serialize($args));
	if ( empty($crons) )
		return false;
	foreach ( $crons as $timestamp => $cron ) {
		if ( isset( $cron[$hook][$key] ) )
			return $cron[$hook][$key]['schedule'];
	}
	return false;
}

//
// Private functions
//

/**
 * Retrieve cron info array option.
 *
 * @since 2.1.0
 * @access private
 *
 * @return false|array CRON info array.
 */
function _get_cron_array()  {
	$cron = get_option('cron');
	if ( ! is_array($cron) )
		return false;

	if ( !isset($cron['version']) )
		$cron = _upgrade_cron_array($cron);

	unset($cron['version']);

	return $cron;
}

/**
 * Updates the CRON option with the new CRON array.
 *
 * @since 2.1.0
 * @access private
 *
 * @param array $cron Cron info array from _get_cron_array().
 */
function _set_cron_array($cron) {
	$cron['version'] = 2;
	update_option( 'cron', $cron );
}

/**
 * Upgrade a Cron info array.
 *
 * This function upgrades the Cron info array to version 2.
 *
 * @since 2.1.0
 * @access private
 *
 * @param array $cron Cron info array from _get_cron_array().
 * @return array An upgraded Cron info array.
 */
function _upgrade_cron_array($cron) {
	if ( isset($cron['version']) && 2 == $cron['version'])
		return $cron;

	$new_cron = array();

	foreach ( (array) $cron as $timestamp => $hooks) {
		foreach ( (array) $hooks as $hook => $args ) {
			$key = md5(serialize($args['args']));
			$new_cron[$timestamp][$hook][$key] = $args;
		}
	}

	$new_cron['version'] = 2;
	update_option( 'cron', $new_cron );
	return $new_cron;
}
