<?php

// Template Tag
function buddyforms_easypin_gallery() {
    echo buddyforms_easypin_get_gallery();
}

function buddyforms_easypin_get_gallery() {
	global $post, $buddyforms, $gallery, $form_slug;

	// Get the Parent post id
	$parent_id = wp_get_post_parent_id( $post->ID );

	// Check if parent is 0. In this case we are the parent and need to use the post id
	$parent_id = $parent_id == 0 ? $post->ID : $parent_id;

	// Get the images from the parent
    $buddyforms_easypin_image = get_post_meta( $parent_id, 'buddyforms_easypin_image', true );

	$form_slug = get_post_meta( $post->ID, '_bf_form_slug', true );

    // Create the json for the frontend
    $easy_init = '';
    if( is_array( $buddyforms_easypin_image ) ){
        foreach ( $buddyforms_easypin_image as $img_id => $cords){
            $easy_init .= '"' . $img_id . '":{';
            $i = 0;
            foreach ($cords as $cord){
                if( !empty( $cord['id'] ) ) {

                    $pin_post = get_post($cord['post_id']);

                    $easy_init .= '"' . $i . '":{';
	                $easy_init .= '"title":"' . $pin_post->post_title . '",';
                    $easy_init .= '"description":"' . $pin_post->post_content . '",';

                    if( isset( $buddyforms[$form_slug]['form_fields'] ) ){
                        foreach( $buddyforms[$form_slug]['form_fields'] as $field_kay => $field){
	                        $easy_init .= '"' . $field['slug'] . '":"' . get_post_meta( $pin_post->ID, $field['slug'], true ). '",';
                        }
                    }

	                $easy_init .= '"permalink":"' . get_the_permalink( $cord['post_id'] ) . '",';
                    $easy_init .= '"coords":{"lat":"' . $cord['lat'] . '","long":"' .  $cord['long'] . '"}},"canvas":{"src":"' . $cord['src'] . '", "width":"' . $cord['width'] . '","height":"' . $cord['height'] . '"},';

                }
                $i++;
            }
            $easy_init = substr($easy_init, 0, -1);
            $easy_init .= '},';
        }
        $easy_init = substr($easy_init, 0, -1);
    }

    // get the file form element we are using for the gallery
    $gallery_string = get_post_meta( $parent_id, 'easypin', true );

    if( empty($gallery_string) ){
        return;
    }

    $gallery = explode( ',', $gallery_string );

    if( ! is_array($gallery) ){
        return;
    }

    ob_start();

	buddyforms_easypin_locate_template('easypin-popover');
    ?>

    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery('.pin').easypinShow({

                data:'{<?php echo $easy_init ?>}',
                responsive: true,
                variables: {
                    <?php
	                if( isset( $buddyforms[$form_slug]['form_fields'] ) ){
		                foreach( $buddyforms[$form_slug]['form_fields'] as $field_kay => $field){
	                        echo $field['slug']. ': function (canvas_id, pin_id, data) { return data;},';
		                }
	                } ?>
                },
                popover: {
                    show: false,
                    animate: true
                },
                each: function (index, data) {
                    return data;
                },
                error: function (e) {
                    console.log(e);
                }
            });
            jQuery('#bf-easypin-carousel').carousel({
                interval: false
            });
            jQuery('#bf-easypin-carousel .item').removeClass('active');
            jQuery('#bf-easypin-carousel .item:first').addClass('active');

            jQuery('#slider-thumbs a:first').addClass('selected');

            // handles the carousel thumbnails
            jQuery('.carousel-selector').click( function(){
                var id_selector = jQuery(this).attr("id");
                var id = id_selector.substr(id_selector.length -1);
                id = parseInt(id);
                jQuery('#bf-easypin-carousel').carousel(id);
                jQuery('[id^=carousel-selector-]').removeClass('selected');
                jQuery(this).addClass('selected');
            });

            // when the carousel slides, auto update
            jQuery('.bf-easypin-carousel').on('slid', function (e) {
                var id = jQuery('.item.active').data('slide-number');
                id = parseInt(id);
                jQuery('.carousel-selector').removeClass('selected');
                jQuery('[id=carousel-selector-'+id+']').addClass('selected');
            });

        });
    </script><?php

	$tmp = ob_get_clean();

	return $tmp;
}


/*
 * Create the Gallery view for the set Image in the edit screen
 */
function buddyforms_edit_easypin($shortcode_args){
	global $wp_query, $buddyforms;

	extract( shortcode_atts( array(
		'post_parent'   => 0,
		'post_id'       => 0,
		'gallery_slug'  => ''
	), $shortcode_args ) );

	// Get the gallery image ids as string
	$gallery_string = get_post_meta( $post_parent, $gallery_slug, true );
	if( empty($gallery_string) ){
		return;
	}

	// Create array of images
	$gallery = explode( ',', $gallery_string );
	if( ! is_array($gallery) ){
		return;
	}

	// get the coordinates
	$easypin_post = get_post_meta( $post_id, 'buddyforms_easypin_post', true );;

	// Create the jason for the coordinates
	$easy_init = '';
	if( is_array( $easypin_post ) ){
		foreach ( $easypin_post as $img_id => $cords){
			if( !empty( $cords['id'] ) ) {
				$easy_init .= '"' . $cords['id'] . '":{"0":{"coords":{"lat":"' . $cords['lat'] . '","long":"' .  $cords['long'] . '"}},"canvas":{"src":"' . $cords['src'] . '", "width":"' . $cords['width'] . '","height":"' . $cords['height'] . '"}},';
			}
		}
		$easy_init = substr($easy_init, 0, -1);
	}

	// create the js amd html
	ob_start();
	?>

    <script>

        jQuery(document).ready(function () {

            inittest();
            jQuery(document).on('click', '.easy-delete', function () {

                jQuery('#easypin-id-<?php echo $img_id ?>').val('');
                jQuery('#easypin-long-<?php echo $img_id ?>').val('');
                jQuery('#easypin-lat-<?php echo $img_id ?>').val('');
                jQuery('#easypin-width-<?php echo $img_id ?>').val('');
                jQuery('#easypin-height-<?php echo $img_id ?>').val('');

            });

            jQuery('#bf-easypin-carousel').carousel({
                interval: false
            });
            jQuery('#bf-easypin-carousel .item').removeClass('active');
            jQuery('#bf-easypin-carousel .item:first').addClass('active');

            jQuery('#slider-thumbs a:first').addClass('selected');


            // handles the carousel thumbnails
            jQuery('.carousel-selector').click( function(){
                var id_selector = jQuery(this).attr("id");
                var id = id_selector.substr(id_selector.length -1);
                id = parseInt(id);
                jQuery('#bf-easypin-carousel').carousel(id);
                jQuery('[id^=carousel-selector-]').removeClass('selected');
                jQuery(this).addClass('selected');
            });

            // when the carousel slides, auto update
            jQuery('.bf-easypin-carousel').on('slid', function (e) {
                var id = jQuery('.item.active').data('slide-number');
                id = parseInt(id);
                jQuery('.carousel-selector').removeClass('selected');
                jQuery('[id=carousel-selector-'+id+']').addClass('selected');
            });

        });

    </script>



    <div  class="row">
        <!-- thumb navigation carousel -->
        <div class="col-md-12 hidden-sm hidden-xs" id="slider-thumbs">
            <!-- thumb navigation carousel items -->
            <ul class="list-inline">
				<?php
				$i = 0;
				foreach( $gallery as $img_id ) {
					$image = wp_get_attachment_image_src( $img_id, "thumbnail" ); ?>
                    <li> <a id="carousel-selector-<?php echo $i ?>" class="carousel-selector">
                            <img class="img-responsive"  src="<?php echo $image[0]; ?>"/>
                        </a></li>
					<?php
					$i++;
				}
				?>
            </ul>
        </div>
    </div>

    <div style="margin-top: 20px" class="row">
        <div id="bf-easypin-carousel" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
				<?php
				$active = 'active';
				foreach( $gallery as $img_id ) {

					$image = wp_get_attachment_image_src( $img_id, "full" ); ?>
                    <div class="item <?php echo $active ?>" data-slide-number="<?php echo $i ?>">

                        <input name="easypin[<?php echo $img_id ?>][post_id]"     id="easypin-post_id-<?php echo $img_id ?>"     type="hidden" value="<?php echo $post_id; ?>">
                        <input name="easypin[<?php echo $img_id ?>][id]"     id="easypin-id-<?php echo $img_id ?>"     type="hidden" value="">
                        <input name="easypin[<?php echo $img_id ?>][src]"    id="easypin-id-<?php echo $img_id ?>"    type="hidden" value="<?php echo $image[0] ?>">
                        <input name="easypin[<?php echo $img_id ?>][long]"   id="easypin-long-<?php echo $img_id ?>"   type="hidden" value="">
                        <input name="easypin[<?php echo $img_id ?>][lat]"    id="easypin-lat-<?php echo $img_id ?>"    type="hidden" value="">
                        <input name="easypin[<?php echo $img_id ?>][width]"  id="easypin-width-<?php echo $img_id ?>"  type="hidden" value="">
                        <input name="easypin[<?php echo $img_id ?>][height]" id="easypin-height-<?php echo $img_id ?>" type="hidden" value="">

                        <img width="100%" src="<?php echo $image[0]; ?>" class="buddyforms-pin" easypin-id="<?php echo $img_id ?>"/>

                    </div>
					<?php
				}
				?>
            </div>
        </div>
    </div>
    <div style="display:none;" popover></div>
    <script>

        function inittest(){
            $instance = jQuery('.buddyforms-pin').easypin({

                init: '{<?php echo $easy_init ?>}',
                limit: 1,
                exceeded: function (element) {
                    alert('You only able to create one pin at the time ;)');
                },
//                responsive: true,
                popover: {
                    show: false,
                },
                drop: function (lat, long, element, parentid) {
                    easypin_set_corts(lat, long, parentid);
                    jQuery(".pinCanvas").remove();
                    jQuery(".popover").remove();

                },
                drag: function (lat, long, element, parentid) {
                    console.log
                    easypin_set_corts(lat, long, parentid);
                    jQuery(".pinCanvas").remove();
                    jQuery(".popover").remove();
                }
            });
            $instance.easypinShow();
        }

        function easypin_set_corts(lat, long, img_id) {

            var image = jQuery("[easypin-id=" + img_id + "]");
            var ep_id = image.attr('easypin-id');

            var width = image.width();
            var height = image.height();

            jQuery('#easypin-id-' + img_id).val(ep_id);
            jQuery('#easypin-long-' + img_id).val(long);
            jQuery('#easypin-lat-' + img_id).val(lat);
            jQuery('#easypin-width-' + img_id).val(width);
            jQuery('#easypin-height-' + img_id).val(height);

            return false;
        }
    </script>

	<?php
	$easypin = ob_get_clean();
	return $easypin;
}


function buddyforms_easypin_locate_template( $slug ) {
	global $buddyforms, $bp, $gallery, $current_user, $form_slug, $post_id;

	// Get the current user so its not needed in the templates
	$current_user  = wp_get_current_user();

	// create the plugin template path
	$template_path = BUDDYFORMS_EASYPIN_TEMPLATE_PATH .'/'. $slug . '.php';

	// Check if template exist in the child or parent theme and use this path if available
	if ( $template_file = locate_template( "{$slug}.php", false, false)) {
		$template_path = $template_file;
	}

	// Do the include
	include( $template_path );

}


add_filter( 'buddyforms_form_element_display_frontend', 'buddyforms_easypin_form_element_display_frontend', 10, 2 );

function buddyforms_easypin_form_element_display_frontend( $meta_tmp, $customfield ){

    if( $customfield['type'] == 'easypin' ){
	    $meta_tmp = buddyforms_easypin_get_gallery();
    }

	return $meta_tmp;
}

add_filter( 'buddyforms_hook_field_types', 'buddyforms_easypin_hook_field_types', 10, 1);
function buddyforms_easypin_hook_field_types( $hook_field_types ){

	array_push( $hook_field_types, "easypin");

	return $hook_field_types;

}