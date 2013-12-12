<?php
/*
Plugin Name: Minimalistic Event Manager
Plugin URI: https://github.com/ms-studio/minimalistic-event-manager/
Description: The plugin allows to add event dates (start dates, end dates) to posts (and to custom post types).
Version: 1.0.4
Author: Dan Stefancu, Manuel Schmalstieg
Author URI: http://dreamproduction.net

By default the plugin adds the custom MEM metabox to all post types, but this can be changed by
calling the settings function (mem_plugin_settings):

	function my_mem_settings() {
		mem_plugin_settings( array( 'post', 'page', 'event' ), 'alpha' );
	}
	add_action( 'mem_init', 'my_mem_settings' );

Where the first parameter is an array of post types, or an array with "all" as the only value,
and the second parameter the edit mode (full/alpha).

The plugin checks for valid post types before adding the metabox.

*/

/**
 * Init the plugin translation
 */
function mem_lang_init() {
	load_plugin_textdomain( 'mem', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}


/**
 * Used to alter the plugin default settings.
 *
 * @param array $post_types all/array of post types
 * @param string $edit_mode full/alpha
 */
function mem_plugin_settings( $post_types = array('all'), $edit_mode = 'full' ) {

	$types = array();

	if ( array_shift(array_values($post_types)) == 'all' ) {
		$types = get_post_types(array('public' => true, 'show_ui' => true));

	} else {
		foreach($post_types as $type) {
			if ( post_type_exists($type) )
				$types[] = $type;
	}
}



	update_option( 'mem_post_types', $types);

	switch ($edit_mode) {
		case 'full' :
			update_option( 'mem_edit_mode', 'full' );
			break;
		case 'alpha' :
			update_option( 'mem_edit_mode', 'alpha' );
			break;
	}
}


/**
 * Add metaboxes for specified post types
 *
 * @uses add_meta_box()
 */
function mem_add_metaboxes(){
	$post_types = get_option( 'mem_post_types' );

	// check needed only on first plugin run, after this, the default option will contain
	// an array of all public post types
	if ( array_shift(array_values($post_types)) == 'all' ) {
		$post_types = get_post_types(array('public' => true, 'show_ui' => true));
	}



	// no need to further validate the data.
	// the function wich alter settings validates post types before saving
	foreach ($post_types as $post_type) {
		add_meta_box('mem', __('Event Dates', 'mem'), 'mem_content', $post_type, 'side', 'core');
	}
}


/**
 * Display metabox content
 *
 * @param object $post post object
 */
function mem_content($post) {

	$start_date = $end_date = '';

	// true as last param for get_post_meta(), to get a string, not an array
	if (! get_post_meta( $post->ID, '_mem_start_date', true ) ) {
		$start_date_label = __('Not Set', 'mem');
		// if no start date, delete repeats leftovers
		delete_post_meta($post->ID, '_mem_repeat_date');
	} else {
		$start_date = $start_date_label = get_post_meta( $post->ID, '_mem_start_date', true );
	}

	if (! get_post_meta( $post->ID, '_mem_end_date', true ) ) {
		$end_date_label = __('Not Set', 'mem');
	} else {
		$end_date = $end_date_label = get_post_meta( $post->ID, '_mem_end_date', true );
	}


	$start_title = __('Start date', 'mem');
	$end_title = __('End date', 'mem');
	/* translators: the title of the Repeat item, not the action button */
	$repeat_title = _x('Repeat', 'repeat item title', 'mem');

	// use default wp class .misc-pub-section for spacing and that thin line below ?>
	<div class="misc-pub-section start">
		<span class="date-title"><?php echo $start_title; ?></span>:
		<strong class="date-label"><?php echo $start_date_label; ?></strong>&nbsp;
		<a href="#edit_timestamp" class="mem-edit-timestamp hide-if-no-js" tabindex='4'><?php _e('Edit', 'mem') ?></a> &nbsp;
		<a href="#edit_timestamp" class="mem-repeat-timestamp hide-if-no-js" tabindex='4'><?php 
		/* translators: the Repeat action button */
		_ex('Repeat', 'repeat action button', 'mem') ?></a>
		<div class='hide-if-js mem-date-select'><?php mem_touch_time( 'start', $start_date ); ?></div>
	</div>

	<?php

	if ( get_post_meta( $post->ID, '_mem_repeat_date', true ) ) {
		$repeats = get_post_meta( $post->ID, '_mem_repeat_date', false );
		$repeat_count = 0;
		foreach ($repeats as $repeat) {
			$repeat_count++;

			$repeat_date = $repeat_date_label = $repeat; ?>

			<div class="misc-pub-section repeat">
				<span class="date-title"><?php echo $repeat_title . " #" . $repeat_count; ?></span>:
				<strong class="date-label"><?php echo $repeat_date_label; ?></strong>&nbsp;
				<a href="#edit_timestamp" class="mem-edit-timestamp hide-if-no-js" tabindex='4'><?php _e('Edit', 'mem') ?></a>
				<div class='hide-if-js mem-date-select'><?php mem_touch_time( 'repeat', $repeat_date, $repeat_count ); ?></div>
			</div>

			<?php

		}
		// we need a method to verify the repeats. there is no limit
		echo "<input type='hidden' name='mem_total_repeats' value='" . $repeat_count . "' id='total-repeats' />";

	}

	// use default wp class .misc-pub-section for spacing and that thin line below ?>
	<div class="misc-pub-section end">
		<span class="date-title"><?php echo $end_title; ?></span>:
		<strong class="date-label"><?php echo $end_date_label; ?></strong>&nbsp;
		<a href="#edit_timestamp" class="mem-edit-timestamp hide-if-no-js" tabindex='4'><?php _e('Edit', 'mem') ?></a>
		<div class='hide-if-js mem-date-select'><?php mem_touch_time( 'end', $end_date ); ?></div>
	</div>


	<?php
	wp_nonce_field(plugin_basename( __FILE__ ), '_mem_nonce');

}

/**
 * Display the date inputs
 *
 * @param string $type start/end
 * @param string $custom_date date to edit, else current date will be used
 * @param int $repeat_count repeat number
 * @return mixed
 */
function mem_touch_time( $type = 'start', $custom_date = '', $repeat_count = 0 ) {
	global $wp_locale;

	$day = $month = $year = $hour = $minute = $split_date = $split_time = '';

	if ($custom_date) {
		list($split_date, $split_time) = explode(" ", $custom_date) + array("","");
		// hack, so the list will not generate undefined index
		// @see http://www.php.net/manual/en/function.list.php#103311
		list($year, $month, $day) = explode("-", $split_date) + array("","","");

	if ( $split_time )
		list($hour, $minute) = explode(":", $split_time) + array("","");
	}
	$aa = ($custom_date) ? $year : '';
	$mm = ($custom_date) ? $month : '';
	$jj = ($custom_date) ? $day : '';

	$hh = ($custom_date) ? $hour : '';
	$mn = ($custom_date) ? $minute : '';

	$month = "<select class=\"mm\" name=\"" . $type . "_mm" . ( $repeat_count ? '_'.$repeat_count : '' ) . "\">\n";
	$month .= "\t\t\t" . '<option value=""';
	$month .= ">-----</option>\n";
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$monthnum = zeroise($i, 2);
		$month .= "\t\t\t" . '<option value="' . $monthnum . '"';
		if ( $i == $mm )
			$month .= ' selected="selected"';
		/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
		$month .= '>' . sprintf( __( '%1$s-%2$s', 'mem' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
	}
	$month .= '</select>';

	$day = '<input type="text" class="jj" name="' . $type . '_jj' . ( $repeat_count ? '_'.$repeat_count : '' ) . '" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';
	$year = '<input type="text" class="aa" name="' . $type . '_aa' . ( $repeat_count ? '_'.$repeat_count : '' ) . '" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';
	$hour = '<input type="text" class="hh" name="' . $type . '_hh' . ( $repeat_count ? '_'.$repeat_count : '' ) . '" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" />';
	$minute = '<input type="text" class="mn" name="' . $type . '_mn' . ( $repeat_count ? '_'.$repeat_count : '' ) . '" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" />';

	echo '<div class="mem-timestamp-wrap">';

	if (get_option( 'mem_edit_mode') == 'alpha') {
		$alpha = '<input type="text" class="full-date-alpha" name="' . $type . '_full-date-alpha' . ( $repeat_count ? '_'.$repeat_count : '' );
		$alpha .= '" value="' . $custom_date  . '" size="32" autocomplete="off" />';
		echo $alpha;
	} else {

		/* translation of the input order. 1 month, 2 day, 3 year, 4 hour, 5 minute */
		printf(__('%1$s%2$s, %3$s @ %4$s : %5$s', 'mem'), $month, $day, $year, $hour, $minute);
	}

	// we need to store the old value for repeats, so we can store multiple repeats
	// @see http://codex.wordpress.org/Function_Reference/update_post_meta#Parameters
	if ($type == 'repeat')
		echo '<input type="hidden" name="repeat_old_value_' . $repeat_count . '" value="' . $custom_date . '" class="old-value" />';

	echo '</div>';

	?>

<div class="mem-edit-buttons">
	<a href="#edit_timestamp" class="mem-save-timestamp hide-if-no-js button"><?php _e('OK', 'mem'); ?></a>
	<a href="#edit_timestamp" class="mem-cancel-timestamp hide-if-no-js"><?php _e('Cancel', 'mem'); ?></a>
	<span class="mem-separator"> | </span>
	<a href="#edit_timestamp" class="mem-delete-timestamp hide-if-no-js"><?php _e('Delete', 'mem'); ?></a>
</div>
<?php
}

/**
 * Saves our date
 *
 * @param int $id post id
 * @param object $post post object
 * @return mixed
 */
function mem_save_date( $id, $post ) {
	$mem_post_types = get_option( 'mem_post_types' );

	if ( isset($mem_post_types[0]) && $mem_post_types[0] == 'all' ) {
		$mem_post_types = get_post_types(array('public' => true, 'show_ui' => true));
	}

	// check for a valid post type
	if ( !in_array($post->post_type, $mem_post_types) )
		return;


	// check nonce
	$nonce = isset($_REQUEST['_mem_nonce']) ? $_REQUEST['_mem_nonce'] : '';
	if ( !wp_verify_nonce( $nonce , plugin_basename( __FILE__ ) ) )
		return;

	// Check permissions
	if ( !current_user_can( 'edit_post', $id ) )
		return;

	$types = array('start', 'end', 'repeat');

	foreach ($types as $type) {

		if ($type == 'repeat') {
			$count_repeats = 1;
			$total_repeats = $_POST['mem_total_repeats'];

			while ( $count_repeats <= $total_repeats ) {

				$year = isset($_POST[$type . '_aa_' . $count_repeats]) ? $_POST[$type . '_aa_' . $count_repeats] : '';
				$month = isset($_POST[$type . '_mm_' . $count_repeats]) ? $_POST[$type . '_mm_' . $count_repeats] : '';
				$day = isset($_POST[$type . '_jj_' . $count_repeats]) ? $_POST[$type . '_jj_' . $count_repeats] : '';
				$hour = isset($_POST[$type . '_hh_' . $count_repeats]) ? $_POST[$type . '_hh_' . $count_repeats] : '';
				$minute = isset($_POST[$type . '_mn_' . $count_repeats]) ? $_POST[$type . '_mn_' . $count_repeats] : '';

				$alpha_date = isset($_POST[$type . '_full-date-alpha_' . $count_repeats]) ? $_POST[$type . '_full-date-alpha_' . $count_repeats] : '';

				// we need this to store multiple meta fields
				// without old values all the meta fields are overwritten by the new value
				$old_value = isset($_POST[$type . '_old_value_' . $count_repeats]) ? $_POST[$type . '_old_value_' . $count_repeats] : '';

				if ( !empty($year) ) {
					$date = $year;

					if ( !empty($month) ) {
						$date .= "-" . zeroise( $month, 2 );
						if ( !empty($day) ) {
							$date .= "-" . zeroise( $day, 2 );
							if ( !empty($hour) ) {
								$date .= " " . zeroise( $hour, 2 );
								if ( !empty($minute) )
									$date .= ":" . $minute;
							}
						}
					}
				} elseif ( !empty($alpha_date) ) {
					$date = $alpha_date;
				} else {
					delete_post_meta($id, '_mem_' . $type . '_date', $old_value);
					$count_repeats++;
					continue;
					// if we delete, we don't need to update, continue
				}

				// don't update if there is a repeat date with the same value in db
				$all_repeats = get_post_meta($id, '_mem_' . $type . '_date', false);

				$all_values = get_post_meta($id);
				$duplicate_found = false;

				// loop the post custom fields
				foreach ( $all_values as $key => $value ) {
					// check to see the custom field is ours
					if ( strpos($key, '_mem') !== false ) {
						// we get an array
						foreach($value as $single_value) {

							if ($single_value == $date) {
								$duplicate_found = true;
								break;
							}
						}
					}
				}
				if ( $duplicate_found ) {
					$count_repeats++;
					continue;
				}


				if ( empty($all_repeats) ) {
					add_post_meta($id, '_mem_' . $type . '_date', $date );
				} else {
						// update or add only if our value isn't in db
						if (!in_array($date,$all_repeats)) {
							if ($old_value)
								update_post_meta($id, '_mem_' . $type . '_date', $date, $old_value );
							else
								add_post_meta($id, '_mem_' . $type . '_date', $date );
						}

				}
				$count_repeats++;

			}

			continue;
		}



		$year = isset($_POST[$type . '_aa']) ? $_POST[$type . '_aa'] : '';
		$month = isset($_POST[$type . '_mm']) ? $_POST[$type . '_mm'] : '';
		$day = isset($_POST[$type . '_jj']) ? $_POST[$type . '_jj'] : '';
		$hour = isset($_POST[$type . '_hh']) ? $_POST[$type . '_hh'] : '';
		$minute = isset($_POST[$type . '_mn']) ? $_POST[$type . '_mn'] : '';

		$alpha_date = isset($_POST[$type . '_full-date-alpha']) ? $_POST[$type . '_full-date-alpha'] : '';

		if ( !empty($year) ) {
			$date = $year;

			if ( !empty($month) ) {
				$date .= "-" . zeroise( $month, 2 );
				if ( !empty($day) ) {
					$date .= "-" . zeroise( $day, 2 );
					if ( !empty($hour) ) {
						$date .= " " . zeroise( $hour, 2 );
						if ( !empty($minute) )
							$date .= ":" . $minute;
					}
				}
			}
		} elseif ( !empty($alpha_date) ) {
			$date = $alpha_date;
		} else {
			delete_post_meta($id, '_mem_' . $type . '_date');
			continue;
		}

		update_post_meta($id, '_mem_' . $type . '_date', $date );
	}

}

function mem_ajax() {
	$action = $_POST['mem_action'];
	$id = $_POST['post_id'];
	$type = $_POST['type'];
	$date = $_POST['date'];
	$old_value = isset($_POST['old_value']) ? $_POST['old_value'] : '';

	switch ($action) {

		case "save" :
			if ($type == 'repeat') {
				// don't update if there is a repeat date with the same value in db
				$all_repeats = get_post_meta($id, '_mem_' . $type . '_date', false);

				if ( empty($all_repeats) ) {
					add_post_meta($id, '_mem_' . $type . '_date', $date );
				} else {

					if (!in_array($date,$all_repeats)) {
						if ($old_value)
							update_post_meta($id, '_mem_' . $type . '_date', $date, $old_value );
						else
							add_post_meta($id, '_mem_' . $type . '_date', $date );
					}

				}
			} else {
				// this is not a repeat
				update_post_meta($id, '_mem_' . $type . '_date', $date );
			}
			break;
		case "delete" :
			if ($old_value)
				delete_post_meta($id, '_mem_' . $type . '_date', $date, $old_value );
			else
				delete_post_meta($id, '_mem_' . $type . '_date', $date );
			break;

	}
}

/**
 * Enqueue js used for metaboxes, add some CSS
 */
function mem_js() {
	wp_enqueue_script( 'mem_js', plugins_url('/js/mem.js', __FILE__), array('jquery') );
	$translation_array = array(
		'not_set' => __('Not Set', 'mem'),
		//'repeat' => __('Repeat', 'mem'),
		'repeat' => _x('Repeat', 'repeat item title', 'mem'),
	);
	// sending ajaxurl as translated string is a hack seen as good practice to send the variable across scripts
	// @see http://www.garyc40.com/2010/03/5-tips-for-using-ajax-in-wordpress/#js-global
	wp_localize_script( 'mem_js', 'mem_l10n', $translation_array );
	wp_enqueue_style( 'mem_css', plugins_url('/css/mem.css', __FILE__));
}

function add_mem_hook() {
	do_action( 'mem_init' );
}

// Register default settings. If options already set, do nothing
add_option( 'mem_edit_mode', 'full' );
$all_post_types = get_post_types(array('public' => true, 'show_ui' => true));
add_option( 'mem_post_types', $all_post_types);

// add the hook as late as possible
add_action( 'admin_init', 'add_mem_hook', 100);
add_action( 'admin_init', 'mem_add_metaboxes', 101 );
add_action( 'admin_enqueue_scripts', 'mem_js' );
add_action( 'save_post', 'mem_save_date', 10, 2);
add_action( 'wp_ajax_mem_data', 'mem_ajax');
add_action( 'plugins_loaded', 'mem_lang_init');