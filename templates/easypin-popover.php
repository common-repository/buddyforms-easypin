<!-- Normal left, right navigation does not work well with easypin. It overlay the pins. So we have decided to use thump -->
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

<!-- Display the images -->
<div style="margin-top: 20px" class="row">
	<div id="bf-easypin-carousel" class="carousel slide" data-ride="carousel">
		<div class="carousel-inner">
			<?php
			$active = 'active';
			$i = 0;
			foreach( $gallery as $img_id ) {

				$image = wp_get_attachment_image_src( $img_id, "full" ); ?>
				<div width="95%" height="95%" class="item <?php echo $active ?>" data-slide-number="<?php echo $i ?>">
					<img width="100%" src="<?php echo $image[0]; ?>" class="pin" easypin-id="<?php echo $img_id ?>"/>
				</div>
				<?php
				$i++;

			}
			?>
		</div>
	</div>
</div>

<!-- Pin HTML -->
<div>
	<div class="easypin" style="width: auto; height: auto;">
		<div style="position: relative; height: 100%;"></div>
	</div>
</div>

<!-- Overlay Popover HTML -->
<div style="display:none;" easypin-tpl="">
	<popover>
		<div class="exPopoverContainer">
			<div class="popBg borderRadius"></div>
			<div class="popBody">

				<?php
				$form_element = buddyforms_get_form_field_by_slug( $form_slug,'easypin' );
				echo isset($form_element['easypin_template']) ? $form_element['easypin_template'] : '';
				?>

				<div class="arrow-down" style="top: 150px;left: 13px;"></div>

			</div>
		</div>
	</popover>

	<!-- Pin HTML -->
	<marker>
		<div class="marker2">+</div>
	</marker>
</div>