<?php
/*
Plugin Name: DX-Watermark
Plugin URI: http://www.daxiawp.com/dx-watermark.html
Description: The pictures automatically add watermark. 图片自动添加水印。
Version: 1.0.4
Author: 大侠WP
Author URI: http://www.daxiawp.com/dx-watermark.html
Copyright: 大侠设计原创插件，任何个人或团体不得擅自修改版权、盗用代码！
*/



class DX_Watermark{
	
	function __construct(){
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'do_watermark' ), 9999 );		//do watermark
		add_action( 'admin_menu', array( $this, 'menu_page' ) );		//add menu page
		add_action( 'plugins_loaded', array( $this, 'chinese' ) );		//load textdomain
		add_action( 'init', array( $this, 'update_preview_data' ) );	//update preview data
	}
	
	//make dir
	function make_dir(){
		$uploads = wp_upload_dir();
		$dw_dir = $uploads['basedir'].'/dw-uploads';
		if( !is_dir( $dw_dir ) ){
			mkdir( $dw_dir );
			mkdir( $dw_dir.'/fonts' );
		}
	}
	
	// create image water
	function image_water( $options='',$args=array() ){
		//data
		$dst_file = $args['dst_file'];
		$src_file = $args['src_file'];
		$alpha = $args[ 'alpha' ];
		$position = $args['position'];
		$im_file = $args[ 'im_file' ];
		
		$dst_data = @getimagesize( $dst_file );
		$dst_w = $dst_data[0];
		$dst_h = $dst_data[1];
		$min_w = isset( $options['min_width'] ) && $options['min_width'] ? $options['min_width'] : 300 ;
		$min_h = isset( $options['min_height'] ) && $options['min_height'] ? $options['min_height'] : 300 ;
		if( $dst_w <= $min_w || $dst_h <= $min_h ) return;
		$dst_mime = $dst_data['mime'];
		$src_data = @getimagesize( $src_file );
		$src_w = $src_data[0];
		$src_h = $src_data[1];
		$src_mime = $src_data['mime'];
		
		//create
		$dst = $this->create_image( $dst_file, $dst_mime );
		$src = $this->create_image( $src_file, $src_mime );
		$dst_xy = $this->position( $position, $src_w, $src_h, $dst_w, $dst_h );
		$merge = $this->imagecopymerge_alpha( $dst, $src, $dst_xy[0], $dst_xy[1], 0, 0, $src_w, $src_h, $alpha );
		if( $merge ){
			$this->make_image( $dst, $dst_mime, $im_file );
		}
		imagedestroy( $dst );
		imagedestroy( $src );
	}
	
	//create text water
	function text_water( $options='', $args=array() ){
		//data
		$file = $args['file'];
		$font = $args['font'];
		$text = $args['text'];
		$alpha = $args['alpha'];
		$size = $args['size'];
		$red = $args['color'][0];
		$green = $args['color'][1];
		$blue = $args['color'][2];
		$position = $args['position'];
		$im_file = $args['im_file'];
		
		$dst_data = @getimagesize( $file );
		$dst_w = $dst_data[0];
		$dst_h = $dst_data[1];
		$min_w = ( isset( $options['min_width'] ) && $options['min_width'] ) ? $options['min_width'] : 300 ;
		$min_h = ( isset( $options['min_height'] ) && $options['min_height'] ) ? $options['min_height'] : 300 ;
		if( $dst_w <= $min_w || $dst_h <= $min_h ) return;
		$dst_mime = $dst_data['mime'];
		
		//create
		$coord = imagettfbbox( $size, 0, $font, $text );
		$w = abs( $coord[2]-$coord[0] ) + 5;
		$h = abs( $coord[1]-$coord[7] ) ;
		$H = $h+$size/2;
		$src = $this->image_alpha( $w, $H );
		$color = imagecolorallocate( $src, $red, $green, $blue );
		$posion = imagettftext( $src, $size, 0, 0, $h, $color, $font, $text );
		$dst = $this->create_image( $file, $dst_mime );
		$dst_xy = $this->position( $position,$w, $H, $dst_w, $dst_h );
 		$merge = $this->imagecopymerge_alpha( $dst, $src, $dst_xy[0], $dst_xy[1], 0, 0, $w, $H, $alpha );
		$this->make_image( $dst, $dst_mime, $im_file );
		imagedestroy( $dst );
		imagedestroy( $src );				
	}
	
	//create image from file
	function create_image( $file, $mime ){
		switch( $mime ){
			case 'image/jpeg' : $im = imagecreatefromjpeg( $file ); break;
			case 'image/png' : $im = imagecreatefrompng( $file ); break;
			case 'image/gif' : $im = imagecreatefromgif( $file ); break;
		}
		return $im;
	}
	
	//make image
	function make_image( $im, $mime, $im_file ){
		switch( $mime ){
			case 'image/jpeg' : {
				$options = get_option( 'dx-watermark-options' );
				$quality = ( isset( $options['jpeg_quality'] ) && $options['jpeg_quality'] ) ? $options['jpeg_quality'] : 95;
				imagejpeg( $im, $im_file, $quality );
				break;
			}
			case 'image/png' : imagepng( $im, $im_file ); break;
			case 'image/gif' : imagegif( $im, $im_file ); break;
		}
	}
	
	//imagecopymerge alpha
	function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $opacity=$pct;
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);
         
        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
         
        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        $merge = imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
		return $merge;
    }		
	
	function image_alpha( $w, $h ){
		$im=imagecreatetruecolor( $w, $h );
		imagealphablending( $im, true );	//启用Alpha合成
		imageantialias( $im, true );	//启用抗锯齿
		imagesavealpha( $im, true );	//启用Alpha通道		
		$bgcolor = imagecolorallocatealpha( $im,255,255,255,127 ); 		//创建透明颜色（最后一个参数0不透明，127完全透明）
		imagefill( $im, 0, 0, $bgcolor );//使图片底色透明
		return $im;
	}
	
	//wartermark position
	function position( $position, $s_w, $s_h, $d_w, $d_h ){
		switch( $position ){
			case 1 : $x=5; $y=0; break;
			case 2 : $x=($d_w-$s_w)/2; $y=0; break;
			case 3 : $x=($d_w-$s_w-5); $y=0; break;
			case 4 : $x=5; $y=($d_h-$s_h)/2; break;
			case 5 : $x=($d_w-$s_w)/2; $y=($d_h-$s_h)/2; break;
			case 6 : $x=($d_w-$s_w-5); $y=($d_h-$s_h)/2; break;
			case 7 : $x=5; $y=($d_h-$s_h); break;
			case 8 : $x=($d_w-$s_w)/2; $y=($d_h-$s_h); break;
			default: $x=($d_w-$s_w-5); $y=($d_h-$s_h); break;
		}
		$res = get_option( 'dx-watermark-options' );
		$x += $res['level'];
		$y += $res['vertical']; 		
		$xy = array( $x, $y );
		return $xy;
	}
	
	//judge dynamic gif
	function IsAnimatedGif( $file ){
		$content = file_get_contents($file);
		$bool = strpos($content, 'GIF89a');
		if($bool === FALSE)
		{
			return strpos($content, chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0')===FALSE?0:1;
		}
		else
		{
			return 1;
		}
	}

	//hex to dec
	function hex_to_dec( $str ){
		$r = hexdec( substr( $str, 1, 2 ) );
		$g = hexdec( substr( $str, 3, 2 ) );
		$b = hexdec( substr( $str, 5, 2 ) );
		$color = array( $r, $g, $b );
		return $color;
	}	
	
	//do watermark
	function do_watermark( $metadata ){
		$options = get_option( 'dx-watermark-options' );
		$upload_dir = wp_upload_dir();
		$dst = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $metadata['file'];
		if( $this->IsAnimatedGif( $dst ) ) return $metadata;
		$src = $options['upload_image'];
		$size= $options['size'] ? $options['size'] : 16;
		$alpha= $options['transparency'] ? $options['transparency'] : 90;
		$position = $options['position'] ? $options['position'] : 9;
		$color = $options['color'] ? $this->hex_to_dec( $options['color'] ) : array(255,255,255);
		$font = $options['font'] ? stripslashes($options['font']) : dirname(__FILE__).'/fonts/arial.ttf';
		$text = $options['text'] ? stripslashes($options['text']) : get_bloginfo('url');
		
		if( $options['type']=='image' ){
			$args=array(
				'dst_file' => $dst,
				'src_file' => $src,
				'alpha' => $alpha,
				'position' => $position,
				'im_file' => $dst
			);	
			$this->image_water( $options, $args );		
		}
		else{
			$args=array(
				'file'=>$dst,
				'font'=>$font,
				'size'=>$size,
				'alpha'=>$alpha,
				'text'=>$text,
				'color'=>$color,
				'position'=>$position,
				'im_file' => $dst
			);
			$this->text_water( $options, $args );	
		}
		return $metadata;
	}
	
	//add menu page
	function menu_page(){
		add_menu_page( 'DX-Watermark', 'DX-Watermark', 'manage_options', 'DX-Watermark', array( $this, 'options_form' ), plugins_url( 'icon.png', __FILE__ ) );
	}
	
	//menu page form
	function options_form(){
		include( 'options-form.php' );
	}
	
	//translate chinese
	function chinese(){
		$moFile = dirname(__FILE__) . "/languages/zh_CN.mo";
		if( get_bloginfo('language')=='zh-CN' ) load_textdomain( 'DX-Watermark', $moFile );
	}
	
	//get default fonts
	function default_fonts(){
		$font_dir = dirname( __FILE__ ).'/fonts/';
		$font_names = scandir( $font_dir );
		unset( $font_names[0] );
		unset( $font_names[1] );
		foreach( $font_names as $font_name ){
			$fonts[$font_name] = $font_dir.$font_name;
		}
		return $fonts;
	}
	
	//get custom fonts
	function custom_fonts(){
		$uploads = wp_upload_dir();
		$font_dir = $uploads['basedir'].'/dw-uploads/fonts/'; 
		if( is_dir( $font_dir ) ){
			$font_names = scandir( $font_dir );
			unset( $font_names[0] );
			unset( $font_names[1] );
			foreach( $font_names as $font_name ){
				$fonts[$font_name] = $font_dir.$font_name;
			}
			return $fonts;			
		}
	}
	
	//text form
	function dw_form(){
		global $DW_options;
?>
<script type="text/javascript" src="<?php echo plugins_url( 'excolor/jquery.modcoder.excolor.js', __FILE__ ); ?>"></script>
<!--form start-->
<p>
	<label><?php _e( 'Ignore', 'DX-Watermark' );?>: </label> 
	<label for="dw-minwidth" style="width:70px;"><?php _e( 'Min Width', 'DX-Watermark' );?>: </label><input type="text" id="dw-minwidth" name="min_width" value="<?php echo $DW_options['min_width'] ? $DW_options['min_width'] : 300; ?>"/> 
	<label for="dw-minheight" style="width:70px;"><?php _e( 'Min Height', 'DX-Watermark' );?>: </label><input type="text" id="dw-minheight" name="min_height" value="<?php echo $DW_options['min_height'] ? $DW_options['min_height'] : 300; ?>"/>
</p>
<p>
	<label><?php _e( 'Position', 'DX-Watermark' );?>: </label>
	<table width="150" border="1" cellpadding="5" bordercolor="#ccc" id="dw-position">
		<tr>
			<td><input type="radio" name="position" value="1" <?php checked( '1', $DW_options['position'] );?>/>1</td>
			<td><input type="radio" name="position" value="2" <?php checked( '2', $DW_options['position'] );?>/>2</td>
			<td><input type="radio" name="position" value="3" <?php checked( '3', $DW_options['position'] );?>/>3</td>
		</tr>
		<tr>
			<td><input type="radio" name="position" value="4" <?php checked( '4', $DW_options['position'] );?>/>4</td>
			<td><input type="radio" name="position" value="5" <?php checked( '5', $DW_options['position'] );?>/>5</td>
			<td><input type="radio" name="position" value="6" <?php checked( '6', $DW_options['position'] );?>/>6</td>
		</tr>
		<tr>
			<td><input type="radio" name="position" value="7" <?php checked( '7', $DW_options['position'] );?>/>7</td>
			<td><input type="radio" name="position" value="8" <?php checked( '8', $DW_options['position'] );?>/>8</td>
			<td><input type="radio" name="position" value="9" <?php checked( '9', $DW_options['position'] ); if( empty($DW_options['position']) ) echo 'checked'; ?>/>9</td>
		</tr>					
	</table>
</p>
<p><label for="dw-level"><?php _e( 'Level Adjustment', 'DX-Watermark' );?>: </label><input type="text" name="level" id="dw-level" size="60" value="<?php echo $DW_options['level'] ? $DW_options['level'] : 0; ?>"/> px <span class="example"><?php _e( '( eg. 5 or -5 )', 'DX-Watermark' );?></span></p>
<p><label for="dw-vertical"><?php _e( 'Vertical Adjustment', 'DX-Watermark' );?>: </label><input type="text" name="vertical" id="dw-vertical" size="60" value="<?php echo $DW_options['vertical'] ? $DW_options['vertical'] : 0; ?>"/> px <span class="example"><?php _e( '( eg. 10 or -10 )', 'DX-Watermark' );?></span></p>
<p class="text-on">
	<label for="dw-fonts"><?php _e( 'Fonts', 'DX-Watermark' );?>: </label>
	<select id="dw-fonts" name="font">
		<?php 
			$default_fonts = $this->default_fonts();
			foreach( $default_fonts as $key=>$default_font ):
		?>
		<option value="<?php echo $default_font ?>" <?php selected( $default_font, stripslashes($DW_options['font']) ); ?>><?php echo $key; ?></option>
		<?php endforeach; ?>
		<?php 
			$custom_fonts = $this->custom_fonts();
			if( $custom_fonts ):
				foreach( $custom_fonts as $key=>$custom_font ):
		?>
		<option value="<?php echo $custom_font ?>" <?php selected( $custom_font, stripslashes($DW_options['font']) ); ?>><?php echo $key; ?></option>
		<?php endforeach; endif; ?>			
	</select>
</p>
<p class="text-on example">
<?php 
	$uploads = wp_upload_dir();
	$custom_fonts = $uploads['basedir'].'/dw-uploads/fonts/';
	if( get_bloginfo('language')=='zh-CN' ) $fonts_note = '(你可以上传中文.ttf字体文件到 '.$custom_fonts.' 目录)';
	else $fonts_note = '(.ttf font files, you can upload to the '.$custom_fonts.' directory)';
	echo $fonts_note;
?>
</p>
<p class="text-on"><label for="dw-text"><?php _e( 'Text', 'DX-Watermark' );?>: </label><input type="text" name="text" id="dw-text" size="60" value="<?php echo $DW_options['text'] ? stripslashes($DW_options['text']) : get_bloginfo('url'); ?>"/></p>
<p class="text-on"><label for="dw-size"><?php _e( 'Size', 'DX-Watermark' );?>: </label><input type="text" name="size" id="dw-size" size="60" value="<?php echo $DW_options['size'] ? $DW_options['size'] : 16; ?>"/> px <span class="example"><?php _e( '( eg. 16 )', 'DX-Watermark' );?></span></p>
<p class="text-on"><label for="dw-color"><?php _e( 'Color', 'DX-Watermark' );?>: </label><input type="text" name="color" id="dw-color" class="excolor" size="60" value="<?php echo $DW_options['color'] ? $DW_options['color'] : '#ffffff'; ?>"/> <span class="example"><?php _e( '( eg. #000000 )', 'DX-Watermark' );?></span></p>
<p><label for="dw-transparency"><?php _e( 'Transparency', 'DX-Watermark' );?>: </label><input type="text" name="transparency" id="dw-transparency" size="60" value="<?php echo $DW_options['transparency'] ? $DW_options['transparency'] : '90'; ?>"/> <span class="example"><?php _e( '( from 0 to 100 )', 'DX-Watermark' );?></span></p>
<p><label for="dw-quality"><?php _e( 'Jpeg quality', 'DX-Watermark' );?>: </label><input type="text" name="jpeg_quality" id="dw-quality" size="60" value="<?php echo $DW_options['jpeg_quality'] ? $DW_options['jpeg_quality'] : '95'; ?>"/></p>
<p class="example"><?php _e( 'ranges from 1 (worst quality, smaller file) to 100 (best quality, biggest file)', 'DX-Watermark' );?></p>
<!--form end-->
<?php		
	}
	
	//upload image
	function uploade_image(){
		if( isset( $_POST['dw-image'] ) && $_POST['dw-image'] ){
			$uploads = wp_upload_dir();
			$dw_dir = $uploads['basedir'].'/dw-uploads';
			$dw_url = $uploads['baseurl'].'/dw-uploads';
			$fileinfo = $_FILES['upload-image'];
			$file = $fileinfo['tmp_name'];
			$des = $dw_dir.'/'.$fileinfo['name'];
			$res = move_uploaded_file( $file, $des);
			if( $res ){
				global $DW_options;
				$DW_options['upload_image'] = $des;
				$DW_options['upload_image_url'] = $dw_url.'/'.$fileinfo['name'];
				update_option( 'dx-watermark-options', $DW_options );
			}
		}
	}
	
	//update options data
	function update_options(){
		if( isset( $_POST['submit'] ) && $_POST['submit'] ){
			$pre = get_option( 'dx-watermark-options' );
			$data = array(
				'type' => $_POST['type'],
				'position' => $_POST['position'],
				'font' => $_POST['font'],
				'text' => $_POST['text'],
				'size' => $_POST['size'],
				'color' => $_POST['color'],
				'level' => $_POST['level'],
				'vertical' => $_POST['vertical'],
				'transparency' => $_POST['transparency'],
				'upload_image' => $pre['upload_image'],
				'upload_image_url' => $pre['upload_image_url'],
				'min_width' => $_POST['min_width'],
				'min_height' => $_POST['min_height'],
				'jpeg_quality' => $_POST['jpeg_quality']
			);
			update_option( 'dx-watermark-options', $data );
			update_option( 'dx-watermark-options-preview', $data );
		}
		return get_option( 'dx-watermark-options' );
	}
	
	//update preview data
	function update_preview_data(){
		if( isset( $_GET['preview'] ) && $_GET['preview']=='data' ){
			update_option( 'dx-watermark-options-preview', $_GET );
		}
	}

	//form bottom action
	function form_bottom(){
		if( get_bloginfo('language')=='zh-CN' ):
?>
	<div id="form-bottom" style="width:650px;border:1px dotted #ddd;background-color:#f7f7f7;padding:10px;margin-top:20px;">
		<p>插件介绍：<a href="http://www.daxiawp.com/dx-watermark.html" target="_blank">http://www.daxiawp.com/dx-watermark.html</a></p>
		<p>wordpress主题请访问<a href="http://www.daxiawp.com" target="_blank">daxiawp</a>，大量大侠wp制作的主题供选择。wordpress定制、仿站、插件开发请联系：<a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=1683134075&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:1683134075:44" alt="点击这里给我发消息" title="点击这里给我发消息">1683134075</a></p>
	</div>	
<?php
		endif;
	}
}

//register activation hook for make dir
register_activation_hook( __FILE__, array( 'DX_Watermark', 'make_dir' ) );

//new DX_Watermark
$DX_Watermark = new DX_Watermark();

//include theme
if( !function_exists('_daxiawp_theme_menu_page') ) include_once( 'theme.php' );