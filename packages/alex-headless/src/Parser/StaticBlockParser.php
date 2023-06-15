<?php
/**
 * Class definition of a parser for static block attributes
 */
namespace Alex\Headless\Parser;

use Symfony\Component\CssSelector\CssSelectorConverter;
use Alex\Headless\Parser\BlockParserInterface;
use WP_Block_Type_Registry;

class StaticBlockParser implements BlockParserInterface
{
	public function __construct(protected \DOMDocument $html, protected CssSelectorConverter $convert_selector) {}

	public function parse(array $block)
	{
		$block_name = $block['blockName'];
		$block_type = WP_Block_Type_Registry::get_instance()->get_registered( $block_name );
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
