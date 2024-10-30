<?php

/*
 * Create the easy pin form element
 * First: add ut to the form element select
 */

add_filter( 'buddyforms_add_form_element_select_option', 'buddyforms_easypin_add_form_element_to_select', 1, 2 );
function buddyforms_easypin_add_form_element_to_select( $elements_select_options ) {
	global $post;

	if ( $post->post_type != 'buddyforms' ) {
		return;
	}

	$elements_select_options['easypin']['label'] = 'EasyPin';
	$elements_select_options['easypin']['class'] = 'bf_show_if_f_type_post';
	$elements_select_options['easypin']['fields']['easypin'] = array(
		'label'     => __( 'EasyPin', 'buddyforms' ),
		'unique'    => 'unique'
	);

	return $elements_select_options;
}

/*
 * Create the new Form Builder Form Element
 * Create the form element settings
 */
add_filter( 'buddyforms_form_element_add_field', 'buddyforms_easypin_create_new_form_builder_form_element', 1, 5 );
function buddyforms_easypin_create_new_form_builder_form_element( $form_fields, $form_slug, $field_type, $field_id ) {
	global $buddyforms;
	$buddyforms_options = $buddyforms;

	switch ( $field_type ) {

		case 'easypin':

			unset( $form_fields );

			$validation_multiple                            = isset( $customfield['validation_multiple'] ) ? $customfield['validation_multiple'] : 0;
			$form_fields['advanced']['validation_multiple'] = new Element_Checkbox( '<b>' . __( 'Only one file or multiple?', 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][validation_multiple]", array( 'multiple' => '<b>' . __( 'Allow multiple file upload', 'buddyforms' ) . '</b>' ), array( 'value' => $validation_multiple ) );

			$allowed_mime_types = array(
				'jpg|jpeg|jpe'     => 'image/jpeg',
				'gif'              => 'image/gif',
				'png'              => 'image/png',
				'bmp'              => 'image/bmp',
				'tif|tiff'         => 'image/tiff',
				'ico'              => 'image/x-icon'
			);

			$data_types                            = isset( $customfield['data_types'] ) ? $customfield['data_types'] : '';
			$form_fields['advanced']['data_types'] = new Element_Checkbox( '<b>' . __( 'Select allowed file Types', 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][data_types]", $allowed_mime_types, array( 'value' => $data_types ) );

			$name = isset( $buddyforms_options[ $form_slug ]['form_fields'][ $field_id ]['name'] ) ? $buddyforms_options[ $form_slug ]['form_fields'][ $field_id ]['name'] : 'Create Gallery';
			$form_fields['general']['name'] = new Element_Textbox( '<b>' . __( 'Name', 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][name]", array( 'value' => $name ) );

			$label_add_gallery = isset( $buddyforms_options[ $form_slug ]['form_fields'][ $field_id ]['label_add_gallery'] ) ? $buddyforms_options[ $form_slug ]['form_fields'][ $field_id ]['label_add_gallery'] : 'Create Gallery';
			$form_fields['general']['label_add_gallery'] = new Element_Textbox( '<b>' . __( 'Label for "Create Gallery"', 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][label_add_gallery]", array( 'value' => $label_add_gallery ) );

			$label_pin_post = isset( $buddyforms_options[ $form_slug ]['form_fields'][ $field_id ]['label_pin_post'] ) ? $buddyforms_options[ $form_slug ]['form_fields'][ $field_id ]['label_pin_post'] : 'Pin Post';
			$form_fields['general']['label_pin_post'] = new Element_Textbox( '<b>' . __( 'Label for "Pin Post"', 'buddyforms' ) . '</b>', "buddyforms_options[form_fields][" . $field_id . "][label_pin_post]", array( 'value' => $label_pin_post ) );

			$form_fields['advanced']['slug'] = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][slug]", 'easypin' );

			$form_fields['general']['type'] = new Element_Hidden( "buddyforms_options[form_fields][" . $field_id . "][type]", $field_type );

			$easypin_template = isset( $buddyforms_options[ $form_slug ]['form_fields'][ $field_id ]['easypin_template'] ) ? $buddyforms_options[ $form_slug ]['form_fields'][ $field_id ]['easypin_template'] : '
<h1>{[title]}</h1>
<div class="popHeadLine"></div>
<div class="popContentLeft">
    {[description]}
    <br><br><br>
    <a href="{[permalink]}">More info</a>
</div>';



			$form_fields['easypin']['template'] = new Element_HTML( '<p><b>PopOver Template</b></p><p>You can use any form element as template tag. {[slug]}. If you use sub forms for the child posts the form elements need to exist in the both forms. The parent and child forms. All pins from one parent will refer to the parent easypin popover template</p><textarea name="buddyforms_options[form_fields][' . $field_id . '][easypin_template]" rows="14" cols="70" type="text" width="100%">' . $easypin_template . '</textarea>');
			break;

	}

	return $form_fields;
}

/*
 * Display the new Form Element in the Frontend Form
 *
 */
add_filter( 'buddyforms_create_edit_form_display_element', 'buddyforms_easypin_create_frontend_form_element', 1, 2 );
function buddyforms_easypin_create_frontend_form_element( $form, $form_args ) {
	global $wp_query;

	extract( $form_args );

	if ( ! isset( $customfield['type'] ) ) {
		return $form;
	}

	switch ( $customfield['type'] ) {
		case 'easypin':

			$slug = $customfield['slug'];


			$name = empty( $customfield['name'] ) ? __( 'Image Gallery' ) : $customfield['name'];

			$label_add_gallery = empty( $customfield['label_add_gallery'] ) ? __('Create Gallery', 'buddyforms') : $customfield['label_add_gallery'];
			$label_pin_post = empty( $customfield['label_pin_post'] ) ? __('Pin Post', 'buddyforms') : $customfield['label_pin_post'];



			if( $post_parent == 0 ){

				$attachment_ids = $customfield_val;

				$str = '<div id="bf_files_container_' . $slug . '" class="bf_files_container"><ul class="bf_files">';

				$attachments = array_filter( explode( ',', $attachment_ids ) );

				if ( $attachments ) {
					foreach ( $attachments as $attachment_id ) {

						$attachment_metadat = get_post( $attachment_id );

						$str .= '<li class="image" data-attachment_id="' . esc_attr( $attachment_id ) . '">

                                    <div class="bf_attachment_li">
                                    <div class="bf_attachment_img">
                                    ' . wp_get_attachment_image( $attachment_id, array( 64, 64 ), true ) . '
                                    </div><div class="bf_attachment_meta">
                                    <p><b>' . __( 'Name: ', 'buddyforms' ) . '</b>' . $attachment_metadat->post_title . '<p>
                                    <p><b>' . __( 'Type: ', 'buddyforms' ) . '</b>' . $attachment_metadat->post_mime_type . '<p>

                                    <p>
                                    <a href="#" class="delete tips" data-slug="' . $slug . '" data-tip="' . __( 'Delete image', 'buddyforms' ) . '">' . __( 'Delete', 'buddyforms' ) . '</a>
                                    <a href="' . wp_get_attachment_url( $attachment_id ) . '" target="_blank" class="view" data-tip="' . __( 'View', 'buddyforms' ) . '">' . __( 'View', 'buddyforms' ) . '</a>
                                    </p>
                                    </div></div>

                                </li>';
					}
				}

				$str .= '</ul>';

				$str .= '<span class="bf_add_files hide-if-no-js">';


				$library_types = $allowed_types = '';
				if ( isset( $customfield['data_types'] ) ) {

					$data_types_array   = Array();
					$allowed_mime_types = get_allowed_mime_types();

					foreach ( $customfield['data_types'] as $key => $value ) {
						$data_types_array[ $value ] = $allowed_mime_types[ $value ];
					}

					$library_types = implode( ",", $data_types_array );
					$library_types = 'data-library_type="' . $library_types . '"';

					$allowed_types = implode( ",", $customfield['data_types'] );
					$allowed_types = 'data-allowed_type="' . $allowed_types . '"';

				}

				$data_multiple = 'data-multiple="false"';
				if ( isset( $customfield['validation_multiple'] ) ) {
					$data_multiple = 'data-multiple="true"';
				}

				$str .= '<a href="#" data-slug="' . $slug . '" ' . $data_multiple . ' ' . $allowed_types . ' ' . $library_types . 'data-choose="' . __( 'Add into', 'buddyforms' ) . '" data-update="' . __( 'Add', 'buddyforms' ) . '" data-delete="' . __( 'Delete', 'buddyforms' ) . '" data-text="' . __( 'Delete', 'buddyforms' ) . '">' . $label_add_gallery . '</a>';
				$str .= '</span>';

				$str .= '</div><span class="help-inline">';
				$str .= $description;
				$str .= '</span>';

				$form->addElement( new Element_HTML( '<div class="bf_field_group"><label for="_' . $slug . '">' ) );

					if ( isset( $customfield['required'] ) ) {
						$form->addElement( new Element_HTML( '<span class="required">* </span>' ) );
					}

					$form->addElement( new Element_HTML( $name . '</label>' ) );
					$form->addElement( new Element_HTML( '<div class="bf_inputs bf-input">' . $str . '</div>' ) );
					$form->addElement( new Element_Hidden( $slug, $customfield_val, array( 'id' => $slug ) ) );

				$form->addElement( new Element_HTML( '</div>' ) );
			} else {

				$form->addElement( new Element_HTML( '<div class="bf_field_group"><label for="_' . $slug . '">' . $label_pin_post . '</label></div>' ) );

				$form->addElement( new Element_HTML( buddyforms_edit_easypin( array( 'post_id' => $post_id, 'post_parent' => $post_parent, 'gallery_slug' => $slug ) ) ) );

			}

            break;

	}

	return $form;

}

/*
 * After submit we need to save the data
 *
 */
add_action( 'buddyforms_after_save_post', 'buddyforms_easypin_after_save_post', 10, 1 );
function buddyforms_easypin_after_save_post( $post_id ) {

	if( ! isset( $_POST['easypin'] ) ){
		return;
	}

	// Save the coordinates in the post for the edit screen
	update_post_meta( $post_id, 'buddyforms_easypin_post', $_POST['easypin'] );

	$parent_id = wp_get_post_parent_id( $post_id );

	if( $parent_id != 0 ){

		$buddyforms_easypin_image = get_post_meta( $parent_id, 'buddyforms_easypin_image', true );

		if( ! is_array( $buddyforms_easypin_image ) ){
			$buddyforms_easypin_image = Array();
		}

		$easypin = $_POST['easypin'];

		if( is_array($easypin) ){
			foreach( $easypin as $p_id => $pin ){
				$pin['post_id'] = $post_id;
				$buddyforms_easypin_image[ $p_id ][$post_id] = $pin;
			}
		}

		update_post_meta( $parent_id,'buddyforms_easypin_image', $buddyforms_easypin_image );

	}
}