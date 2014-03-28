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

class mem_event_list extends WP_Widget {

		/**
		 * Register widget with WordPress.
		 */
		function __construct() {
			
			parent::__construct(
				'mem_event_list', // Base ID
				__('Event List (mem)', 'mem'), // Name
				array( 
					'description' => __( 'Display a list of events', 'mem' ), 
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
					$show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
			
					// title field
			?>
					<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
			<?php 
					// Number of posts to show
			 ?>
					<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
					<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
			<?php 
					// Age of posts
			 ?>
					<p><label for="<?php echo $this->get_field_id( 'age' ); ?>"><?php _e( 'Age of posts (in days):' ); ?></label>
					<input id="<?php echo $this->get_field_id( 'age' ); ?>" name="<?php echo $this->get_field_name( 'age' ); ?>" type="text" value="<?php echo $age; ?>" size="3" /></p>		
			<?php 
					// Show past events?
			 ?>
					<p><input class="checkbox" type="checkbox" <?php checked( $show_past_events ); ?> id="<?php echo $this->get_field_id( 'show_past_events' ); ?>" name="<?php echo $this->get_field_name( 'show_past_events' ); ?>" />
					<label for="<?php echo $this->get_field_id( 'show_past_events' ); ?>"><?php _e( 'Display past events?', 'mem' ); ?></label></p>
				<?php 
						// Display the date?
				 ?>
					<p><input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
					<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display event date?', 'mem' ); ?></label></p>
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
					
					$mem_today = mem_date_of_today();
					$mem_age = ( $age * DAY_IN_SECONDS );
					$mem_unix_limit = ( $mem_today["unix"] - $mem_age );
					$mem_age_limit = date( "Y-m-d", $mem_unix_limit);
			
					$r = new WP_Query( apply_filters( 'widget_posts_args', array( 
						'posts_per_page' => $number, 
						'meta_key' => '_mem_start_date',
						'meta_value' => $mem_age_limit,
						'meta_compare' => '>=',
						'orderby' => 'meta_value',
						'order' => 'DESC', // DESC = newest first
						'no_found_rows' => true, 
						'post_status' => 'publish', 
						'ignore_sticky_posts' => true, 
						) ) );
					if ($r->have_posts()) :
					
					// initialzize new array:
					$mem_event_list = array();
					
			?>
					<?php echo $before_widget; ?>
					<?php if ( $title ) echo $before_title . $title . $after_title; ?>
					<ul>
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
							 
							 // check for REPEAT fields, add them also
							 	
							 	$date_repeats = get_post_meta(get_the_ID(), '_mem_repeat_date', false);
							 	
							 	if ($date_repeats) {
							 		foreach($date_repeats as $date_repeat) {
							 				
							 				// TEST if it is fresh enough
							 				if ( $date_repeat >= $mem_age_limit ) {
							 					
							 					// format date
							 					$mem_repeat_date =  mem_date_processing( 
							 						$date_repeat, '' );
							 					
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
							
							// do something with the $mem_event_list
							
							// 1. re-order based on start-iso
							
							function mem_custom_sort_iso($a,$b) {
									return $a['start-unix']>$b['start-unix'];
							}
							usort($mem_event_list, "mem_custom_sort_iso");
							
							// 2. generate the frontend output
							
							foreach ($mem_event_list as $key => $item) {
							
									?>
									<li>
										<span class="post-date"><?php 
											echo $mem_event_list[$key]["date-num"];
										?> &ndash; </span>
									<a href="<?php echo $mem_event_list[$key]["permalink"]; ?>">	
											<?php echo $mem_event_list[$key]["title"]; ?></h4></a>
									</li>
											<?php 
							}
					
					?>
					</ul>
					<?php 
					
					echo $after_widget; ?>
			<?php
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