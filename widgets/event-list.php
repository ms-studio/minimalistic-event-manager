<?php

/**
 * A Widget that produces a list of events
 *
 * since MEM 1.0.5
 *
 * The markup is similar to the generic Recent_Posts widget.
 *
 * Options:
 * 
 * * Max number of events to show
 * * Also show past events? 
 * * Age/Limit in days? 0 = no past events.
 * * Date display format? 31.12.2014?
 *
 * Inspiration taken from:
 * /wp-includes/
 *
 **/

function mem_custom_sort_iso($a,$b) {
		return $a['start-unix']>$b['start-unix'];
}

class mem_event_list extends WP_Widget {
		
		
		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			
			parent::__construct(
				'mem_event_list', // Base ID
				__('Event List (mem)', 'minimalistic-event-manager'), // Name
				array( 
					'description' => __( 'Display a list of events', 'minimalistic-event-manager' ), 
					'classname' => 'mem_event_list',
				) // Args
			); // parent::__construct
			
			// Refreshing the widget's cached output with each new post
			add_action( 'save_post',    array( $this, 'flush_widget_cache' ) );
			add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
			add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
			
		} // __construct()
		

		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		function form( $instance ) {
		
					$title     = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
					$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
					$age       = isset( $instance['age'] ) ? absint( $instance['age'] ) : 7;
					$show_past_events = isset( $instance['show_past_events'] ) ? (bool) $instance['show_past_events'] : false;
					$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
					$post_types = isset( $instance['post_types'] ) ? esc_attr( $instance['post_types'] ) : 'post';
			
					// 1: title field
			?>
					<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
			<?php 
					// 2: Number of posts to show
			 ?>
					<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to display:' ); ?></label>
					<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
			<?php 
					// 3: Age of posts
			 ?>
					<p><label for="<?php echo $this->get_field_id( 'age' ); ?>"><?php _e( 'Age of posts (in days):' ); ?></label>
					<input id="<?php echo $this->get_field_id( 'age' ); ?>" name="<?php echo $this->get_field_name( 'age' ); ?>" type="text" value="<?php echo $age; ?>" size="3" /></p>		
			<?php 
					// 4: Show past events?
			 ?>
					<p><input class="checkbox" type="checkbox" <?php checked( $show_past_events ); ?> id="<?php echo $this->get_field_id( 'show_past_events' ); ?>" name="<?php echo $this->get_field_name( 'show_past_events' ); ?>" />
					<label for="<?php echo $this->get_field_id( 'show_past_events' ); ?>"><?php _e( 'Display past events?', 'minimalistic-event-manager' ); ?></label></p>
				<?php 
						// 5: Display the date?
				 ?>
					<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
					<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display event date?', 'minimalistic-event-manager' ); ?></label></p>
			<?php
						// 6: Post types to display
			?>
					<p><label for="<?php echo $this->get_field_id( 'post_types' ); ?>"><?php _e( 'Post types to display:' ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( 'post_types' ); ?>" name="<?php echo $this->get_field_name( 'post_types' ); ?>" type="text" value="<?php echo $post_types; ?>" /></p>
			<?php
			
		} // function form()

	/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		function update( $new_instance, $old_instance ) {
		
			$instance = $old_instance;
					$instance['title'] = strip_tags($new_instance['title']);
					$instance['number'] = (int) $new_instance['number'];
					$instance['age'] = (int) $new_instance['age'];
					$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
					$instance['show_past_events'] = isset( $new_instance['show_past_events'] ) ? (bool) $new_instance['show_past_events'] : false;
					$instance['post_types'] = strip_tags($new_instance['post_types']);
					
					$this->flush_widget_cache();
			
					$alloptions = wp_cache_get( 'alloptions', 'options' );
					if ( isset($alloptions['widget_mem_events_list']) )
						delete_option('widget_mem_events_list');
			
					return $instance;
			
		} // function update()

		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		function widget( $args, $instance ) {
		
					// Check if there is a cached output
					$cache = wp_cache_get('widget_mem_events_list', 'widget');
			
					if ( !is_array($cache) )
						$cache = array();
			
					if ( ! isset( $args['widget_id'] ) )
						$args['widget_id'] = $this->id;
			
					if ( isset( $cache[ $args['widget_id'] ] ) ) {
						echo $cache[ $args['widget_id'] ];
						return;
					}
			
					ob_start();
					extract($args);
			
					$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts' );
					$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
					
					$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
					if ( ! $number )
						$number = 5;
					
					$age = ( ! empty( $instance['age'] ) ) ? absint( $instance['age'] ) : 7;
					if ( ! $age )
						$age = 7;
					
					$show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
					
					$show_past_events = isset( $instance['show_past_events'] ) ? $instance['show_past_events'] : false;
					
					$post_types = ( ! empty( $instance['post_types'] ) ) ? $instance['post_types'] : 'post';
					$post_types = explode(',', $post_types);
					
					$mem_today = mem_date_of_today();
					
					// user defined age limit. default = 7 days.
					$mem_age = ( $age * DAY_IN_SECONDS );
					$mem_age_limit_unix = ( $mem_today["unix"] - $mem_age );
					$mem_age_limit_iso = date( "Y-m-d", $mem_age_limit_unix);					
					
					// arbitrary six months limit for filtering out old events.
					$mem_six_months = ( 180 * DAY_IN_SECONDS );
					$mem_180day_unix = ( $mem_today["unix"] - $mem_six_months );
					$mem_180day_iso = date( "Y-m-d", $mem_180day_unix);
										
					$r = new WP_Query( apply_filters( 'widget_posts_args', array( 
						'post_type' => $post_types,
						'posts_per_page' => -1, // $number ... will be used later!
						'meta_key' => '_mem_start_date',
						'meta_value' => $mem_180day_iso,
						'meta_compare' => '>=',
						'orderby' => 'meta_value',
						'order' => 'DESC', // DESC = newest first
						'no_found_rows' => true, 
						'post_status' => 'publish', 
						'ignore_sticky_posts' => true, 
						) ) );
					if ($r->have_posts()) :
					
					// initialize new array to store events
					$mem_event_list = array();
					
			?>
					
					<?php while ( $r->have_posts() ) : $r->the_post(); ?>
					<?php 
										// add item to array.
										// we need: 
										// 1. start date in iso-format
										// 2. date for display (date-num)
										// 3. title
										// 4. permalink
							
							$mem_event_date =  mem_date_processing( 
								get_post_meta(get_the_ID(), '_mem_start_date', true) , 
								get_post_meta(get_the_ID(), '_mem_end_date', true)
							);
							
							$mem_event_list[] = array( 
							    	"start-unix" => $mem_event_date["start-unix"],
							    	"date-num" => $mem_event_date["date-num"],
							    	"title" => get_the_title(),
							    	"permalink" => get_permalink(),
							 );
							 
							 // check for "Repeat" fields, add them to the list as unique events.
							 	
							 	$date_repeats = get_post_meta(get_the_ID(), '_mem_repeat_date', false);
							 	
							 	if (!empty($date_repeats)) {
							 		foreach($date_repeats as $date_repeat) {
							 				
							 				// test if the item is fresh enough
							 				if ( $date_repeat >= $mem_age_limit_iso ) {
							 					
							 					// format date
							 					$mem_repeat_date =  mem_date_processing( $date_repeat, '' );
							 					
							 					// add item to array
							 					$mem_event_list[] = array( 
							 					    	"start-unix" => $mem_repeat_date["start-unix"],
							 					    	"date-num" => $mem_repeat_date["date-num"],
							 					    	"title" => get_the_title(),
							 					    	"permalink" => get_permalink(),
							 					 );
							 					
							 				}
							 		}     	
							 	}
							 // end testing for REPEATS
						
					endwhile; 
							
							// Filtering the $mem_event_list
							
							
							// 1. re-order the dates, based on start-unix
							
							usort($mem_event_list, "mem_custom_sort_iso");
							
							// 2. filter out OLD dates, with $mem_age_limit_unix
							
							$mem_event_list = array_filter( $mem_event_list, 
									function($i) use ($mem_age_limit_unix) { 
											return $i['start-unix'] >= $mem_age_limit_unix; 
							});
							
							if ( count($mem_event_list) <= $number ) {

								// The total amount of events fits our limit - great!
								// Nothing else to do, continue to next step.
							
							} else {

								// Too many events, we have to trim them...
								// Let's calculate the number of FUTURE events:
								
								$mem_future_event_list = array_filter( $mem_event_list, 
										function($i) use ( $mem_today ) { 
												return $i['start-unix'] >= $mem_today["unix"]; 
								});
								
								
								/*
								 * Note: there's an edge case that we don't handle:
								 * Too many past events, but very little future events
								 * We could calculate exactly how many past events we can show...
								 * Work for volunteers!
								*/
								
								if ( count($mem_future_event_list) <= $number ) {
									
									// Total of FUTURE events fits our limit - great!
									// Redefine array to use:
									
									$mem_event_list = $mem_future_event_list;
									
									// Nothing else to do, continue to next step.
									
								} else {

									// Too many upcoming events...
									// We need to shorten our array!
									
									$mem_event_list = array_slice($mem_future_event_list, 0, $number);
									
								}
								// 
							}
							
							// 3. Finally, generate the frontend output
							
							if (!empty($mem_event_list) ) {
									
									echo $before_widget;
									if ( $title ) echo $before_title . $title . $after_title;
									echo '<ul>';
							
									foreach ($mem_event_list as $key => $item) {
									
											?>
											<li>
												<?php 
													if ($show_date == true) {
														echo '<span class="post-date">';
														echo apply_filters(
															'mem_event_date_display',
															$mem_event_list[$key]["date-num"],
															$mem_event_list[$key]
														);
														echo ' &ndash; </span>';
													} 
												?>
											<a href="<?php echo $mem_event_list[$key]["permalink"]; ?>">	
													<?php echo $mem_event_list[$key]["title"]; ?></a>
											</li>
													<?php 
									}
									
									echo '</ul>';
									echo $after_widget;
									
							}
					
					// Reset the global $the_post as this query will have stomped on it
					wp_reset_postdata();
			
					endif;
			
					$cache[$args['widget_id']] = ob_get_flush();
					wp_cache_set('widget_mem_events_list', $cache, 'widget');
			
		}	// function widget
		
		function flush_widget_cache() {
			wp_cache_delete('widget_mem_events_list', 'widget');
		}
	
	
} // class mem_event_list

// register widget

function register_mem_event_list_widget(){
	register_widget('mem_event_list');	
}
add_action('widgets_init', 'register_mem_event_list_widget');