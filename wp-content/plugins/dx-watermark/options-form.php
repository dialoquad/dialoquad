<?php 
	//update options and get options value
	global $DW_options;
	$DW_options = $this->update_options();
	$this->uploade_image();
?>

<style type="text/css">
#dx-watermark{ width:650px; background-color:#f7f7f7; border:1px solid #ddd; padding:10px; margin-top:20px; float:left; margin-right:20px; }
#dx-watermark label{ width:120px; display:inline-block; }
#show-preview{ width:500px; height:339px; float:left; }
<?php if( $DW_options['type']!='image' ):?>
.text-on{display:block;}
#dw-upload{display:none;}
<?php else: ?>
.text-on{display:none;}
#dw-upload{display:block;}
<?php endif;?>
.submit{ padding:0; margin:20px 0; }
#dx-watermark .example{ color:gray; }
</style>

<div class="wrap">

	<div id="icon-options-general" class="icon32"><br></div><h2><?php _e( 'DX-Watermark Options', 'DX-Watermark' );?></h2>
	
	<div id="dx-watermark">
		<form action="" method="post" id="dw-form">
			<div id="dw-switch">			
				<label><?php _e( 'Type', 'DX-Watermark' ); ?>: </label>
				<input type="radio" name="type"  value="text" <?php checked( 'text', $DW_options['type'] ); if( empty( $DW_options['type'] ) ) echo 'checked'; ?>/> <?php _e( 'text', 'DX-Watermark' ); ?>
				<input type="radio" name="type" value="image" <?php checked( 'image', $DW_options['type'] ); ?>/> <?php _e( 'image', 'DX-Watermark' ); ?>
			</div>
			<?php $this->dw_form(); ?>			
			<?php submit_button(); ?>
		</form>
		<form action="" method="post" id="dw-upload" enctype="multipart/form-data">
			<label for="dw-image"><?php _e( 'Upload Image', 'DX-Watermark' ); ?>: </label>
			<input type="file" name="upload-image" id="dw-image"/> <input type="submit" name="dw-image" value="<?php _e( 'Upload Image', 'DX-Watermark' ); ?>"/>
			<input type="hidden" id="upload-file" value="<?php echo $DW_options['upload_image']; ?>"/>
			<div id="show-image">
				<?php if( $DW_options['upload_image_url'] ): ?>
				<img src="<?php echo $DW_options['upload_image_url']; ?>"/>
				<?php else: ?>
				<span style="color:red"><?php _e( 'You have not upload watermark image!', 'DX-Watermark' ); ?></span>
				<?php endif; ?>
				<p><?php _e( 'Please upload the background transparent png images.', 'DX-Watermark' ); ?></p>
			</div>
		</form>
	</div>
	
	<div id="preview-box">
		<p><input type="button" id="dx-preview" class="button button-secondary" value="<?php _e( 'Click Preview', 'DX-Watermark' ); ?>"/></p>
		<div id="show-preview"><img src="<?php echo plugins_url( 'preview.php', __FILE__ ); ?>"/></div>
	</div>
	
	<div style="clear:both;"></div>
	
	<?php $this->form_bottom(); ?>

</div>

<script type="text/javascript">
jQuery(document).ready(function($){
	
	/*switch*/
	$('#dw-switch input').change(function(){
		var sSwitch = $(this).val();
		if( sSwitch == 'image' ){
			$('.text-on').css('display','none');
			$('#dw-upload').css('display','block');
		}
		else{
			$('.text-on').css('display','block');
			$('#dw-upload').css('display','none');
		}
	});
	
	/*preview*/
	$('#dx-preview').click(function(){
		var sLoading = '<?php echo plugins_url( 'loading.gif', __FILE__ ); ?>';
		$('#show-preview img').attr('src',sLoading);
		$.ajaxSetup({cache:false});
		$.get(
			'<?php bloginfo( 'url' ); ?>/?preview=data',
			{
				size : $('#dw-size').val(),
				color : $('#dw-color').val(),
				position : $('#dw-position input:checked').val(),
				font : $('#dw-fonts').val(),
				transparency : $('#dw-transparency').val(),
				level : $('#dw-level').val(),
				vertical : $('#dw-vertical').val(),
				text : $('#dw-text').val(),
				type : $('#dw-switch input:checked').val(),
				upload_image : $('#upload-file').val(),
				jpeg_quality : $('#dw-quality').val()
			},
			function(data){
				var sWatermark = '<?php echo plugins_url( 'preview.php', __FILE__ ); ?>?t=' + Math.random();
				$('#show-preview img').attr('src',sWatermark);
			}
		);
	});
	
	/*color picker*/
	$('.excolor').modcoder_excolor({
	   sb_slider : 1,
	   effect : 'zoom',
	   callback_on_ok : function() {
		  // You can insert your code here 
	   }
	});	
	
});
</script>