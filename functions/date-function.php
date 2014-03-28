<?php 

/**
 * MEM Date of Today
 *
 * @since 1.0.5
 *
 * OUTPUT: returns an array with date information
 *
 */

function mem_date_of_today() {

   // current year info
   $mem_current_year = date("Y");
   
   // build an ARRAY to return:
   $mem_dates_array = array(
       "today-now" => date_i18n( "j F Y - H:i:s"),
       "today" => date_i18n( "j F Y"),
       "today" => date("Y-m-d"),
       "unix" => strtotime( date("Y-m-d") ),
       "isoweek" => date("W"),
       // current year info
       "current-year" => $mem_current_year,
       "current-year-string" => $mem_current_year . "-01-01",
       "current-year-end" => $mem_current_year . "-12-31 23:59",
   );
   
   return $mem_dates_array;
   
}

/**
 * MEM Date Processing
 *
 * @since 1.0.5
 *
 * @param string $start_date
 * @param string $end_date
 *
 * OUTPUT: returns an array with date information
 *
 */

// NOTE: regarding translation:
// http://codex.wordpress.org/I18n_for_WordPress_Developers

// Use the textdomain of the MEM plugin: 
// example: __('Event Dates', 'mem')
// example: _x('Event Dates', 'context explanation', 'mem')


function mem_date_processing($start_date, $end_date) {

			// initalize our variables
			$start_date_iso = $start_date;
			$end_date_iso = $end_date;
			
			$event_date = '';
			$event_date_short = '';
			$event_date_num = '';
			$event_date_yr = '';
			
			$start_year = '';
			$end_year = '';
			
			$unix_start = '';
			$unix_end = '';
			
			$event_is_future = false;
			$event_is_ongoing = false;
			
			$ndash = '<span class="ndash">–</span>';
						
				
			// step 1 // test and define start date values
			
			if ($start_date !== "" ) { 
					
					if (strlen($start_date) > 5) { 
					
							// the MONTH is defined
							
							if (strlen($start_date) > 7) {
							
									// the DAY is defined 
							
									if (strlen($start_date) > 10) { 
									
											// the TIME is defined
											
											$start_date_iso = substr_replace($start_date, 'T', 10 , 1);
											// replace space with T in iso string	// 2013-03-11T06:35
											
									} else { // 10
									
									// the DAY is defined, but no time
									
									} // 10
									
							} else { // 7 
							
									// the MONTH is defined, but no DAY
									// fix $start_date_iso  - add day.
									
									$start_date_iso .= '-01';
							
							} // 7
							
							$unix_start = strtotime($start_date_iso);
							$start_year = date( "Y", $unix_start);
							$start_month = date_i18n( "F", $unix_start);
					
					} else { // 5
					
							// only the YEAR is defined
					
							$event_date = $start_date;
							$start_year = $start_date;
							
							// NOTE: 
							// $unix_start is not yet defined.
							// let's create a fake ISO / Unix_Start.
							
							$start_date_iso .= '-01-01';
							$unix_start = strtotime($start_date_iso);
							
					} // 5
					
					$event_date_yr = $start_year;
					
			}
			
			// step 2 // test and define END date values
		
		if ($end_date !== "" ) { 
								
				if (strlen($end_date) > 5) { 
						
						// the MONTH is defined
				
					if (strlen($end_date) > 7) { 
					
						// the DAY is defined
					
						if (strlen($end_date) > 10) { 
								
								// the TIME is defined
								
								$end_date_iso = substr_replace($end_date, 'T', 10 , 1);
								
						} else {
						
								// day is defined, but no time
						
						} // 10
							
					} else { // 7
							
							// the MONTH is defined, but no day
							
							$end_date_iso .= '-31';
							
					} // 7
					
						$unix_end = strtotime($end_date_iso);
						
						$end_year = date( "Y", $unix_end);
						$end_month = date_i18n( "F", $unix_end);
				
				} else { // 5
				
						// only the YEAR is defined
				
						$end_year = $end_date;
						
						// fix for $unix_end:
						
						$end_date_iso .= '-12-31';
						$unix_end = strtotime($end_date_iso);
						
				} // 5
				
				if ($end_year != $start_year ) {
					$event_date_yr .= $ndash . $end_year;
				}
				
		} else { 
		
				// no end date defined at all.
				
				$end_date_iso = $start_date_iso;
				$end_year = $start_year;
				$unix_end = $unix_start;
				
				/* 
				
					Q: 
				 		Why do we define those values if no end date exists?
				  
				  A: 
				 		Because this allows us to order a series of events by end date,
				 		even if the end date isn't defined.
				 		Important: we don't change the $end_date value.
				 		
				*/
		
		}
			
			// 3) process the values
			
			// condition 1: do we have a start date?
			
			if ($start_date !== "" ) {
					
				// Yes? - Condition 2: do we have more than 5 chars ?
				// **************************************************
				
				if (strlen($start_date) > 5) { // yes = MONTH is defined
				
					// Yes? - Condition 3: do we have an end date ?
					// ********************************************
							
						if ($end_date !== "" ) {
						
							// YES, we have START and END date.
							// ********************************
												
							// Condition 4: does start/end occur in the same year?
							// ***************************************************
							
							if ($start_year == $end_year) { // YES, same YEAR!
								
								// Condition 5: do start/end occur the same month?
								// ***********************************************
								
								if ($start_month == $end_month) { // YES, same MONTH!
								
								  // test if start DAY is defined
			
									// 4) test if start/end occurs the same day
									// ********************************
									
									$start_day = date( "j", $unix_start); // j = 1 to 31
									$end_day = date( "j", $unix_end);
									
									$event_date_short = date_i18n( "F Y", $unix_start);
									
									if ($start_day == $end_day) { // yes, same day! 
									
											// 5) the events must have a different time
											// *****************************************
											
												if ( (date( "j", $unix_start)) == 1) { // 1er
												  $event_date = date_i18n( _x('l F jS Y, g:i a','First day of month','mem'), $unix_start);
												} else {
												  $event_date = date_i18n( _x('l F jS Y, g:i a','Other day of month','mem'), $unix_start);
												}
												
												// add the end time
												 $event_date .= $ndash . date( __('g:i a','mem'), $unix_end);
												 
												// numbers only: 31.12.2014 
												 $event_date_num = date( __('j.n.Y','mem'), $unix_start);
									
									} else { // two different days, but same month.
									
												// March 4th-15th 2013
										
												if ( (date( "j", $unix_start)) == 1) { // 1er
												  $event_date = date_i18n( _x( 'F jS', 'First day of same month', 'mem' ), $unix_start); // \D\u j\e\\r
												} else {
												  $event_date = date_i18n( _x( 'F jS', 'Other day of same month', 'mem' ), $unix_start); // Du 3 ...	// \D\u j
												}
												
												// end = cannot be 1st!
												
												  $event_date .= date_i18n( __( '–jS Y', 'mem' ), $unix_end);	
												// au 17 mars 2012
												
												$event_date_num = date( __('j','mem'), $unix_start);
												$event_date_num .= $ndash . date( __('j.n.Y','mem'), $unix_end);
									
									} // end condition 6 // start day equals end day.
									
								} else { // ELSE condition 5 // two different MONTHS, but same year
								
									$event_date_short = date_i18n( "F", $unix_start); // janvier
									$event_date_short .= $ndash . date_i18n( "F Y", $unix_end); // - mars 2012
								
									// condition 6 // TEST if the start DAY is definded
									// ************************************************
									
									if (strlen($start_date) > 7)  {
									
											if ( (date_i18n( "j", $unix_start)) == 1) { // 1er
											  $event_date = date_i18n( _x( 'F jS', 'First day of diff month', 'mem' ), $unix_start);
											} else { // sinon
											  $event_date = date_i18n( _x( 'F jS', 'Other day of diff month', 'mem' ), $unix_start);	
											}
											
											// process end date.
											
											if ( (date_i18n( "j", $unix_end)) == 1) { // 1er
											  $event_date .= date_i18n( _x( ' – F jS Y', 'First day of month', 'mem' ), $unix_end);
											} else { // sinon
											  $event_date .= date_i18n( _x( ' – F jS Y', 'Other day of month', 'mem' ), $unix_end);	
											}
											
											// numbers: 18.9-5.10.2014
											$event_date_num = date( __('j.n–','mem'), $unix_start);
											$event_date_num .= date( __('j.n.Y','mem'), $unix_end);
										
									} else {
											// Start DAY not defined = output only the month.
											$event_date = $event_date_short;
											
											$event_date_num = date( __('n','mem'), $unix_start);
											$event_date_num .= $ndash . date( __('n.Y','mem'), $unix_end);
											
											// note: some weird cases aren't covered here. 
											
									} // END condition 6
									
								} // END condition 5 // END month testing
								
								
							} else { // ELSE condition 4 // 
							
							// START YEAR different from END YEAR
							// **********************************
							
										// note: we KNOW that start month IS defined.
										$event_date_short = date_i18n( "F Y", $unix_start); // janvier 2010 ...
								
								// condition 5-A // is START DAY defined?
								// **************************************
								
								if (strlen($start_date) > 7) { // START DAY is defined
								
								   // December 15th 2013 - January 3rd 2014 
								
										if ( (date_i18n( "j", $unix_start)) == 1) { // 1er
										  $event_date = date_i18n( _x( 'F jS Y', 'First day of diff year', 'mem' ), $unix_start);
//										  $event_date = date_i18n( "\D\u j F Y", $unix_start); // 3 janvier 2010 ...
										} else { // sinon
										  $event_date = date_i18n( _x( 'F jS Y', 'Other day of diff year', 'mem' ), $unix_start);
//										  $event_date = date_i18n( "\D\u j F Y", $unix_start); // 3 janvier 2010 ...	
										}
										
										$event_date_num = date( __('j.n.Y','mem'), $unix_start);
										
								} else { // START DAY is not defined, only the MONTH.
										
										$event_date = $event_date_short;
										
										$event_date_num = date( __('n.Y','mem'), $unix_start);
										$event_date_num .= $ndash . date( __('n.Y','mem'), $unix_end);
										
								} // close condition 5-A // START DAY
								
								
								  // condition 5-B // is the END DAY defined?
								  // ****************************************
								
								if (strlen($end_date) > 7) { // END DAY is defined
								
//										$event_date .= date_i18n( " \a\u j F Y", $unix_end); // 17 mars 2012
										if ( (date_i18n( "j", $unix_end)) == 1) { // 1er
										  $event_date .= date_i18n( _x( ' – F jS Y', 'First day of month', 'mem' ), $unix_end);
										} else { // sinon
										  $event_date .= date_i18n( _x( ' – F jS Y', 'Other day of month', 'mem' ), $unix_end);	
										}
										
										$event_date_num .= $ndash . date( __('j.n.Y','mem'), $unix_end);
										
										$event_date_short .= ' '.$ndash .' '. date_i18n( "F Y", $unix_end); // mars 2012
								
								} else if (strlen($end_date) > 5) { // only END MONTH is defined
								
										$event_date_short .= $ndash . date_i18n( "F Y", $unix_end); // mars 2012
										$event_date = $event_date_short;
										
										$event_date_num .= $ndash . date( __('n.Y','mem'), $unix_end);
								
								} else { // only END YEAR is defined
								
										$event_date_short .= $ndash . date( "Y", $unix_end); // mars 2012
										$event_date = $event_date_short;
										
										$event_date_num .= $ndash . date( __('Y','mem'), $unix_end);
										
								} // END condition 5-B // testing the end_date
							
							} // END condition 4 // $start_year == $end_year // END year testing
							
						} else { // ELSE condition 3 // 
						
						// END date is not defined.
						// we have ONLY a START date.
						// **************************
						
								$event_date_short = date_i18n( "F Y", $unix_start); // janvier 2010
						
								// condition 4 // test if START DAY is defined.
							
								if (strlen($start_date) > 7) {				
									
									$event_date_num = date( __('j.n.Y','mem'), $unix_start);
									
									// condition 5 // test if TIME is defined.
								
									if (strlen($start_date) > 11) {
											
												if ( (date( "j", $unix_start)) == 1) { // 1st day of month ?
												  $event_date = date_i18n( _x( 'l F jS Y, g:i a', 'First day of month', 'mem' ), $unix_start);
												} else {
												  $event_date = date_i18n( _x( 'l F jS Y, g:i a', 'Other day of month', 'mem' ), $unix_start);	
												}
											
										} else { // condition 5 // START TIME is not defined.
										
												if ( (date( "j", $unix_start)) == 1) { // 1st day of month ?
												  $event_date = date_i18n( _x('l, F jS Y', 'First day of month', 'mem'), $unix_start);
												} else {
												  $event_date = date_i18n( _x('l, F jS Y', 'Other day of month', 'mem'), $unix_start);
												}
										} // END condition 5 // end testing for TIME.
									
								} else { // ELSE condition 4 // START DAY is not defined.
								
									$event_date = $event_date_short;
									
									$event_date_num = date( __('n.Y','mem'), $unix_start);
								
								} // END condition 4 // end testing for START DAY //
								
							} // END condition 3 (end_date testing) //
						
					} else { // condition 2 // start date > 5 // START YEAR only is defined //
					
						// For YEAR ONLY display: 
						// Test if we should show the END year.
						// ************************************
						
						if ($end_date !== "" ) { 
								if ($end_year != $start_year ) {
									$event_date .= $ndash . $end_year ;
								}
						}
						
						$event_date_short = $event_date;
						$event_date_num = $event_date;
										
				} // END condition 2 (YEAR only)
					
		} // END condition 1.
		
		
		// is this a future event?
		$mem_today = mem_date_of_today();
		
		//global $mem_unix_now;
		
		if ( $mem_today["unix"] <= $unix_start ) {
			$event_is_future = true;
		}
		
		// is this an ongoing event?
				
		if ( $mem_today["unix"] <= $unix_end ) {
			$event_is_ongoing = true;
		}
		
			// build an ARRAY to return:
			
			$event_date_array = array(
			    "date" => $event_date, // Jeudi 19 septembre 2013
			    "date-short" => $event_date_short,
			    "date-num" => $event_date_num,
			    
			    "start-iso" => $start_date_iso,
			    "end-iso" => $end_date_iso,
			    
			    "start-unix" => $unix_start,
			    "end-unix" => $unix_end,
			    
			    "date-year" => $event_date_yr, // can be 2012-2013
			    "start-year" => $start_year,
			    "end-year" => $end_year,
			    
			    "is-future" => $event_is_future, // true or false
			    "is-ongoing" => $event_is_ongoing // true or false
			);
			
			return $event_date_array;

} // end of function

      
?>