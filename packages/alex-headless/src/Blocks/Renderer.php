<?php
/**
 * Class definition for dynamic block renderer
 *
 * @package alex/alex-headless
 */

namespace Alex\Headless\Blocks;

/**
 * Renders dynamic blocks as structured data
 */
class Renderer {
	/**
	 * The core/image block render callback
	 *
	 * @param array $attributes     Image block attributes.
	 */
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
