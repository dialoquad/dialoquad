<?php
/*  Copyright 2011  Andreas Norman

	This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Generalx Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

	Plugin Name: Norman Advanced Archive Widget
	Plugin URI: http://www.andreasnorman.se/norman-archive-widget
	Description: Norman Advanced Archive Widget is a free replacement for the standard WordPress archive widget. Lots of customization options to satisfy your needs.
	Author: Andreas Norman
	Version: 1.1
	Author URI: http://www.andreasnorman.se
 */

class NormanArchiveWidget extends WP_Widget {

	function NormanArchiveWidget() {
		parent::WP_Widget(false, $name = 'Norman Adv. Archive Widget');	
	}

	function get_years($current_category_id) {
		global $wpdb;

		if ($current_category_id) {
	    	$where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id IN ($current_category_id)");
			$join = apply_filters('getarchives_join', " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)");
		} else {
	    	$where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish'");
			$join = apply_filters('getarchives_join', "");
		}

    	$sql = "SELECT DISTINCT YEAR(post_date) AS `year`, count(ID) as posts ";
    	$sql .="FROM {$wpdb->posts} {$join} {$where} ";
    	$sql .="GROUP BY YEAR(post_date) ORDER BY post_date DESC";

    	return $wpdb->get_results($sql);
	}

	function get_months($year, $current_category_id) {
	    global $wpdb;

		if ($current_category_id) {
		    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) = {$year} AND $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id IN ($current_category_id)");
			$join = apply_filters('getarchives_join', " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)");
		} else {
		    $where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) = {$year}");
		    $join = apply_filters('getarchives_join', "");
		}

	    $sql = "SELECT DISTINCT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts ";
	    $sql .="FROM {$wpdb->posts} {$join} {$where} ";
	    $sql.= "GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC";

	    return $wpdb->get_results($sql);
	}

	function get_posts($year, $month, $current_category_id) {
    	global $wpdb;

    	if (empty($year) || empty($month))
        	return null;

		if ($current_category_id) {
	    	$where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) = {$year} AND MONTH(post_date) = {$month} AND $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id IN ($current_category_id)");
			$join = apply_filters('getarchives_join', " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)");
		} else {
	    	$where = apply_filters('getarchives_where', "WHERE post_type = 'post' AND post_status = 'publish' AND YEAR(post_date) = {$year} AND MONTH(post_date) = {$month}");
	    	$join = apply_filters('getarchives_join', "");
		}

    	$sql = "SELECT ID, post_title, post_name FROM {$wpdb->posts} ";
    	$sql .="$join $where ORDER BY post_date DESC";

    	return $wpdb->get_results($sql);
	}	

	function widget($args, $instance) {
		global $wp_locale;
    	extract( $args );
		$plugin_url = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) );
		$showcount = empty($instance['showcount']) ? 0 : $instance['showcount'];
		$linkcounter = empty($instance['linkcounter']) ? 0 : $instance['linkcounter'];
		$truncmonth = empty($instance['truncmonth']) ? 0 : $instance['truncmonth'];
		$jsexpand = empty($instance['jsexpand']) ? 0 : $instance['jsexpand'];
		$groupbyyear = empty($instance['groupbyyear']) ? 0 : $instance['groupbyyear'];
		$limitbycategory = empty($instance['limitbycategory']) ? 0 : $instance['limitbycategory'];
		#$hideonnoncategory = empty($instance['hideonnoncategory']) ? 0 : $instance['hideonnoncategory'];
		$title = empty($instance['title']) ? 'Archive' : $instance['title'];

		if ($limitbycategory) {
			$current_category_id = get_query_var('cat');
		} else {
			$current_category_id = false;
		}

		if ($jsexpand == 1) {
			$groupbyyear = 1;
		}
		if ($groupbyyear == 1) {
			$jsexpand = 1;
		}

		$years = $this->get_years($current_category_id);
		$post_year = $years[0]->year;

		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo '<ul>';
		$index = 0;
		for ($i = 0; isset($years[$i]) && 0 < count($years[$i]); $i++) {
		//for ($i = 0; $x < count($years[$i]); $i++) {
			$this_year = $years[$i]->year == $post_year;
			//$this_year = $jal_options['expandcurrent'] && $years[$i]->year == $post_year;
			$months = $this->get_months($years[$i]->year, $current_category_id);

			if ($groupbyyear) {
				$year_url = get_year_link($years[$i]->year);

				$count_text = '';
				if ($showcount) {
					$count_text = " ({$years[$i]->posts})";
				}

				echo '<li class="norman-adv-archive-year norman-adv-archive-year-groupby"><a class="icon more" rel="'.$years[$i]->year.'" href="#"><span>+</span></a><a href="'.$year_url.'">'.$years[$i]->year;
				if ($linkcounter) {
					echo $count_text.'</a>';
				} else {
					echo '</a>'.$count_text;
				}
				echo '<ul class="'.$years[$i]->year.'-monthlist">';
				foreach ($months as $key => $month) {
					if($index > 10){
						break;
					}
					$index++;
					$month_url = get_month_link($years[$i]->year, $month->month);
					$this_month = $this_year && ( $month == $months[0]);
					//$this_month = $this_year && (($post_id >= 0 && $month->month == $post_month) || ($post_id < 0 && $month == $months[0]));
					$count_text = '';
					if ($showcount) {
						$count_text = " ({$month->posts})";
					}
					if ($truncmonth) {
						$monthname = $wp_locale->get_month_abbrev($wp_locale->get_month($month->month));
					} else {
						$monthname = $wp_locale->get_month($month->month);
					}

					echo '<li><a href="'.$month_url.'">'.$monthname.' '.$years[$i]->year;
					if ($linkcounter) {
						echo $count_text.'</a>';
					} else {
						echo '</a>'.$count_text;
					}
					echo '</li>';
				}
				echo '</ul></li>';
			} else {
				foreach ($months as $key => $month) {
					if($index > 10){
						break;
					}
					$index++;

					$month_url = get_month_link($years[$i]->year, $month->month);
										
					$this_month = $this_year && ( $month == $months[0]);
					//$this_month = $this_year && (($post_id >= 0 && $month->month == $post_month) || ($post_id < 0 && $month == $months[0]));
					$count_text = '';
					if ($showcount) {
						$count_text = " ({$month->posts})";
					}
					if ($truncmonth) {
						$monthname = $wp_locale->get_month_abbrev($wp_locale->get_month($month->month));
					} else {
						$monthname = $wp_locale->get_month($month->month);
					}

					echo '<li><a href="'.$month_url.'">'.$monthname.' '.$years[$i]->year;
					if ($linkcounter) {
						echo $count_text.'</a>';
					} else {
						echo '</a>'.$count_text;
					}
					echo '</li>';
				}
			}
		}	
		echo '</ul>';
		echo $after_widget;
  	}

	function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		$instance['showcount'] = strip_tags($new_instance['showcount']);
		$instance['linkcounter'] = strip_tags($new_instance['linkcounter']);
		$instance['truncmonth'] = strip_tags($new_instance['truncmonth']);
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['jsexpand'] = strip_tags($new_instance['jsexpand']);
		$instance['groupbyyear'] = strip_tags($new_instance['groupbyyear']);
		$instance['limitbycategory'] = strip_tags($new_instance['limitbycategory']);
		#$instance['hideonnoncategory'] = strip_tags($new_instance['hideonnoncategory']);

		return $instance;
	}

	function form($instance) {
		$title = empty($instance['title']) ? 'Archive' : esc_attr($instance['title']);
		$showcount = empty($instance['showcount']) ? 0 : esc_attr($instance['showcount']);
		$linkcounter = empty($instance['linkcounter']) ? 0 : esc_attr($instance['linkcounter']);
		$truncmonth = empty($instance['truncmonth']) ? 0 : esc_attr($instance['truncmonth']);
		$jsexpand = empty($instance['jsexpand']) ? 0 : esc_attr($instance['jsexpand']);
		$groupbyyear = empty($instance['groupbyyear']) ? 0 : esc_attr($instance['groupbyyear']);
		$limitbycategory = empty($instance['limitbycategory']) ? 0 : esc_attr($instance['limitbycategory']);
		#$hideonnoncategory = empty($instance['hideonnoncategory']) ? 0 : esc_attr($instance['hideonnoncategory']);

		if ($jsexpand == 1) {
			$groupbyyear = 1;
		}
		if ($groupbyyear == 1) {
			$jsexpand = 1;
		}
?>

<p>
  <label for="<?php echo $this->get_field_id('title'); ?>">
    <?php _e('Title:'); ?>
  </label>
  <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
</p>
<p>
  <input <?php echo ($limitbycategory=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('limitbycategory'); ?>" name="<?php echo $this->get_field_name('limitbycategory'); ?>" type="checkbox" value="1" />
  <label for="<?php echo $this->get_field_id('limitbycategory'); ?>">
    <?php _e('Limit to current category'); ?>
  </label>
</p>

<!--
    <p>
      <input <?php echo ($hideonnoncategory=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('hideonnoncategory'); ?>" name="<?php echo $this->get_field_name('hideonnoncategory'); ?>" type="checkbox" value="1" />
      <label for="<?php echo $this->get_field_id('hideonnoncategory'); ?>"><?php _e('Hide on none category pages'); ?></label> 
    </p>
-->
<p>
  <input <?php echo ($groupbyyear=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('groupbyyear'); ?>" name="<?php echo $this->get_field_name('groupbyyear'); ?>" type="checkbox" value="1" />
  <label for="<?php echo $this->get_field_id('groupbyyear'); ?>">
    <?php _e('Group By Year (Collaps list)'); ?>
  </label>
</p>
<p>
  <input <?php echo ($jsexpand=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('jsexpand'); ?>" name="<?php echo $this->get_field_name('jsexpand'); ?>" type="checkbox" value="1" />
  <label for="<?php echo $this->get_field_id('jsexpand'); ?>">
    <?php _e('Collaps list (Groups by year)'); ?>
  </label>
</p>
<p>
  <input <?php echo ($showcount=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('showcount'); ?>" name="<?php echo $this->get_field_name('showcount'); ?>" type="checkbox" value="1" />
  <label for="<?php echo $this->get_field_id('showcount'); ?>">
    <?php _e('Show Count'); ?>
  </label>
</p>
<p>
  <input <?php echo ($linkcounter=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('linkcounter'); ?>" name="<?php echo $this->get_field_name('linkcounter'); ?>" type="checkbox" value="1" />
  <label for="<?php echo $this->get_field_id('linkcounter'); ?>">
    <?php _e('Include count in link'); ?>
  </label>
</p>
<p>
  <input <?php echo ($truncmonth=='1'?'checked="checked"':''); ?> id="<?php echo $this->get_field_id('truncmonth'); ?>" name="<?php echo $this->get_field_name('truncmonth'); ?>" type="checkbox" value="1" />
  <label for="<?php echo $this->get_field_id('truncmonth'); ?>">
    <?php _e('Truncate month name'); ?>
  </label>
</p>
<?php 
	}
}

function NormanArchiveWidget_script() {
	$plugin_url = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) );
	wp_register_script( 'SZArchiveWidget_script', $plugin_url.'/script.js');
  	wp_enqueue_script( 'SZArchiveWidget_script' );
}    

function NormanArchiveWidget_style() {
	$plugin_url = plugins_url ( plugin_basename ( dirname ( __FILE__ ) ) );
	wp_register_style('SZArchiveWidget_style', $plugin_url.'/styles.css');
  	wp_enqueue_style( 'SZArchiveWidget_style');
}

add_action('wp_enqueue_scripts', 'NormanArchiveWidget_script');
add_action('wp_print_styles', 'NormanArchiveWidget_style');

add_action('widgets_init', create_function('', 'return register_widget("NormanArchiveWidget");'));
?>
