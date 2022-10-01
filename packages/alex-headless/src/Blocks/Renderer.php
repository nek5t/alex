<?php

namespace Alex\Headless\Blocks;

class Renderer
{
	public function __construct()
	{
		$this->html = new \DOMDocument('1.0', 'UTF-8');
	}

	public function render_block_core_archives($attributes)
	{
		[
			'type' => $type,
			'showPostCounts' => $show_post_count
		] = $attributes;

		$archives_args = apply_filters(
			'widget_archives_args',
			array(
				'type'            => $type,
				'show_post_count' => $show_post_count,
			)
		);

		$archives_args = array_merge(
			$archives_args,
			array(
				'format' => 'link',
				'echo' => false,
			)
		);

		$archives = wp_get_archives($archives_args);
		$links = array();

		$xpath = new \DOMXPath($this->load_html($archives));
		$link_nodes = $xpath->query('//link');

		foreach ($link_nodes as $node) {
			array_push(
				$links,
				array(
					'title' => $node->getAttribute('title'),
					'href' => $node->getAttribute('href')
				)
			);
		}

		return array(
			'links' => $links
		);
	}

	public function render_block_core_avatar($attributes)
	{
		[
			'userId' => $user_id,
			'size' => $size
		] = $attributes;

		$query_attributes = array(
			'alt',
			'src',
			'srcset',
			'loading'
		);
		$avatar = '';

		if (!$user_id) {
			// Get avatar from block context.
		} else {
			$avatar = get_avatar($user_id, $size);
		}

		$xpath = new \DOMXPath($this->load_html($avatar));

		$img = $xpath->query('//img')->item(0);
		$avatar_attributes = array();

		foreach ($query_attributes as $attr_name) {
			$avatar_attributes[$attr_name] = $img->getAttribute($attr_name);
		}

		return $avatar_attributes;
	}

	public function render_block_core_image($attributes)
	{
		$attrs = array();

		if (!empty($attributes['id'])) {
			$image_meta = get_post_meta($attributes['id'], '_wp_attachment_metadata', true);

			// Unset EXIF data.
			unset($image_meta['image_meta']);

			$attrs['image'] = $image_meta;
		}

		return $attrs;
	}

	private function load_html($html)
	{
		$content = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		$this->html->loadHtml($content, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR);

		return $this->html;
	}

	private function save_html($node)
	{
		return $this->html->saveHTML($node);
	}

	private function innerHTML($node)
	{
		$html = '';
		$childNodes = $node?->childNodes ?: array();

		foreach ($childNodes as $childNode) {
			$html .= $this->save_html($childNode);
		}

		return $html;
	}
}
