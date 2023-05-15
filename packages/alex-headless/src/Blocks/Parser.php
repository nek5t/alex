<?php
/**
 * Gutenberg block parser class definition
 *
 * @package alex/alex-headless
 */

namespace Alex\Headless\Blocks;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Alex\Headless\Blocks\Renderer;

/**
 * A parser that extracts attributes and values from block markup.
 */
class Parser {

	/**
	 * Setup class dependencies
	 */
	public function __construct() {
		$this->html             = new \DOMDocument( '1.0', 'UTF-8' );
		$this->convert_selector = new CssSelectorConverter();
		$this->block_registry   = \WP_Block_Type_Registry::get_instance();
		$this->block_renderer   = new Renderer();
	}

	/**
	 * Remove blocks that are unnamed or unregistered,
	 * then transform block content into structured data
	 *
	 * @param array $blocks     An array of parsed block content.
	 */
	public function prepare_blocks( $blocks ) {
		$blocks = array_filter(
			$blocks,
			function ( $b ) {
				return ! empty( $b['blockName'] ) && $this->block_registry->get_registered( $b['blockName'] );
			}
		);

		foreach ( $blocks as &$block ) {
			$name       = $block['blockName'];
			$block_type = $this->block_registry->get_registered( $name );

			/**
			 * Apply current and default attribute values
			 */
			$block['attrs'] = $block_type->prepare_attributes_for_render( $block['attrs'] );

			/**
			 * Attach information about whether the block is static or dynamic
			 */
			$block['dynamic'] = $block_type->is_dynamic();

			/**
			 * Extract attribute values saved in block markup
			 */
			foreach ( $block_type->attributes as $attr_name => $attr ) {
				if ( ! empty( $attr['source'] ) ) {
					$attr_value = call_user_func_array(
						array( $this, 'get_source_' . $attr['source'] ),
						array( $block['innerHTML'], $attr )
					);

					$value = $attr_value;

					$block['attrs'] = array_merge(
						$block['attrs'],
						array( $attr_name => $value )
					);
				}
			}

			/**
			 * Return dynamic rendered props
			 *
			 * Searches the block renderer for a method that matches
			 * the render callback of the given dynamic block.
			 */
			if ( $block_type->is_dynamic() && method_exists( $this->block_renderer, $block_type->render_callback ) ) {
				$block['attrs'] = array_merge(
					$block['attrs'],
					call_user_func( array( $this->block_renderer, $block_type->render_callback ), $block['attrs'] )
				);
			}

			// Filter out attributes that have no value.
			$block['attrs'] = array_filter(
				$block['attrs'],
				function ( $v ) {
					return null !== $v;
				}
			);

			// Recursively call this method over inner blocks.
			$block['innerBlocks'] = $this->prepare_blocks( $block['innerBlocks'] );

			// Remove attributes that will not be used as front-end props.
			$unset_props = apply_filters( 'alex_unwanted_props', ALEX_UNUSED_PROPS );

			foreach ( $unset_props as $prop ) {
				unset( $block[ $prop ] );
			}
		}

		return array_values( $blocks );
	}

	/**
	 * Parse block attributes that have been saved as HTML
	 *
	 * @param string $html   The block markup.
	 * @param array  $attr   The attribute settings, including CSS selector.
	 */
	private function get_source_html( $html, $attr ) {
		$xpath        = new \DOMXPath( $this->load_html( $html ) );
		$selector     = $this->convert_selector->toXPath( $attr['selector'] );
		$node_list    = $xpath->query( $selector );
		$context_node = $node_list->item( 0 );
		$result       = array();

		if ( $context_node && ! empty( $attr['multiline'] ) ) {
			$multi_selector = $this->convert_selector->toXPath( $attr['multiline'] );
			$lines          = $xpath->query( $multi_selector, $context_node );

			foreach ( $lines as $node ) {
				array_push( $result, $this->inner_html( $node ) );
			}
		} else {
			array_push( $result, $this->inner_html( $context_node ) );
		}

		return array_values( array_filter( $result ) );
	}

	/**
	 * Parse block attributes that have been saved as HTML attributes
	 *
	 * @param string $html   The block markup.
	 * @param array  $attr   The attribute settings,
	 *                       including CSS selector and attribute name.
	 */
	private function get_source_attribute( $html, $attr ) {
		$xpath     = new \DOMXPath( $this->load_html( $html ) );
		$attribute = $attr['attribute'];
		$selector  = $this->convert_selector->toXPath( $attr['selector'] );
		$node_list = $xpath->query( $selector );

		return $node_list
			->item( 0 )
			?->attributes
			->getNamedItem( $attribute )
			?->nodeValue;
	}

	/**
	 * Parse block attributes saved as text
	 *
	 * @param string $html   The block markup.
	 * @param array  $attr   The attribute settings, including CSS selector.
	 */
	private function get_source_text( $html, $attr ) {
		$xpath     = new \DOMXPath( $this->load_html( $html ) );
		$selector  = $this->convert_selector->toXPath( $attr['selector'] );
		$node_list = $xpath->query( $selector );

		return $node_list->item( 0 )?->textContent;
	}

	/**
	 * Retrieve block attributes saved as a DOM query
	 *
	 * @param string $html   The block markup.
	 * @param array  $attr   The attribute settings, including CSS selector
	 *                       and list item query parameters.
	 */
	private function get_source_query( $html, $attr ) {
		$xpath     = new \DOMXPath( $this->load_html( $html ) );
		$selector  = $this->convert_selector->toXPath( $attr['selector'] );
		$node_list = $xpath->query( $selector );
		$result    = array();

		foreach ( $node_list as $node ) {
			$node_html = $this->save_html( $node );

			foreach ( $attr['query'] as $key => $query ) {
				$query_result = call_user_func_array(
					array( $this, 'get_source_' . $query['source'] ),
					array( $node_html, $query )
				);
				array_merge(
					array( $key => $query_result ),
					$result
				);
			}
		}

		return $result;
	}

	/**
	 * Load HTML into a DOMDOcument instance for processing
	 *
	 * @param string $html  The HTML fragment to load.
	 * @return DOMDocument
	 */
	private function load_html( $html ) {
		$content = mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' );
		$this->html->loadHtml( $content, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR );

		return $this->html;
	}

	/**
	 * Save contents of a DOM node as HTML
	 *
	 * @param DOMNode $node The DOM node to render as a string.
	 * @return string
	 */
	private function save_html( $node ) {
		return $this->html->saveHTML( $node );
	}

	/**
	 * Return the inner HTML of a DOM node
	 *
	 * @param DOMNode $node A DOM node to process.
	 * @return string $html The HTML of that node's children.
	 */
	private function inner_html( $node ) {
		$html = '';
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$child_nodes = $node?->childNodes;

		if ( ! empty( $child_nodes ) ) {
			foreach ( $child_nodes as $child_node ) {
				$html .= $this->save_html( $child_node );
			}
		}

		return $html;
	}
}
