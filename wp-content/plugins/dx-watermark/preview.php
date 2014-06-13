<?php

$wp_load = dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php';
include_once( $wp_load );
include_once( 'dx-watermark.php' );

class DX_Watermark_Preview extends DX_Watermark{
	//do watermark
	function do_watermark(){
		$options = get_option( 'dx-watermark-options-preview' );
		$dst = dirname(__FILE__).'/preview.jpg';
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
			$this->image_water( '', $args );		
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
			$this->text_water( '', $args );	
		}
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
		$res = get_option( 'dx-watermark-options-preview' );
		$x += $res['level'];
		$y += $res['vertical']; 		
		$xy = array( $x, $y );
		return $xy;
	}	
	
	//make image
	function make_image( $im ){
		$options = get_option( 'dx-watermark-options-preview' );
		$quality = ( isset( $options['jpeg_quality'] ) && $options['jpeg_quality'] ) ? $options['jpeg_quality'] : 95;		
		imagejpeg( $im, NULL, $quality );
	}	
}

/* header ("Cache-Control: no-cache, must-revalidate"); */
header ("Pragma: no-cache");
header( 'Content-type: image/png' );
$DX_preview = new DX_Watermark_Preview();
$DX_preview->do_watermark();