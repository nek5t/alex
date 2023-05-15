<?php

namespace Alex\Headless\Blocks;

class Renderer {
	public function render_block_core_image( $attributes ) {
		$attrs = array();

		if ( ! empty( $attributes['id'] ) ) {
			$image_meta = get_post_meta( $attributes['id'], '_wp_attachment_metadata', true );

			// Unset EXIF data.
			unset( $image_meta['image_meta'] );

			$attrs['image'] = $image_meta;
		}

		return $attrs;
	}
}
