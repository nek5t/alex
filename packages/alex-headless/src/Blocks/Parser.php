<?php

namespace Alex\Headless\Blocks;

use Symfony\Component\CssSelector\CssSelectorConverter;

class Parser
{

	public function __construct()
	{
		$this->html = new \DOMDocument();
		$this->convert_selector = new CssSelectorConverter();
		$this->block_registry = \WP_Block_Type_Registry::get_instance();
	}

	public function prepare_blocks($blocks)
	{
		$blocks = array_filter($blocks, function ($b) {
			return !empty($b['blockName']) && $this->block_registry->get_registered($b['blockName']);
		});

		foreach ($blocks as &$block) {
			$name = $block['blockName'];
			$block_type = $this->block_registry->get_registered($name);

			foreach ($block_type->attributes as $attr_name => $attr) {
				$value = null;
				$hasValue = isset($block['attrs'][$attr_name]);

				if ($hasValue) {
					$value = $block['attrs'][$attr_name];
				}

				if (!empty($attr['source'])) {
					$attr_value = call_user_func_array(
						array($this, 'get_source_' . $attr['source']),
						array($block['innerHTML'], $attr)
					);

					$value = $attr_value;
				}

				if (null === $value && isset($attr['default'])) {
					$value = $attr['default'];
				}

				$block['attrs'] = array_merge(
					$block['attrs'],
					array($attr_name => $value)
				);
			}

			$block['attrs'] = array_filter($block['attrs'], function ($v) {
				return null !== $v;
			});

			$block['innerBlocks'] = $this->prepare_blocks($block['innerBlocks']);

			$unset_props = apply_filters('alex_unwanted_props', ALEX_UNUSED_PROPS);

			foreach ($unset_props as $prop) {
				unset($block[$prop]);
			}
		}

		return array_values($blocks);
	}

	private function get_source_html($html, $attr)
	{
		$xpath = new \DOMXPath($this->load_html($html));
		$selector = $this->convert_selector->toXPath($attr['selector']);
		$nodeList = $xpath->query($selector);
		$context_node = $nodeList->item(0);
		$result = array();

		if ($context_node && !empty($attr['multiline'])) {
			$multi_selector = $this->convert_selector->toXPath($attr['multiline']);
			$lines = $xpath->query($multi_selector, $context_node);

			foreach ($lines as $node) {
				array_push($result, $this->innerHTML($node));
			}
		} else {
			array_push($result, $this->innerHTML($context_node));
		}

		return array_values(array_filter($result));
	}

	private function get_source_attribute($html, $attr)
	{
		$xpath = new \DOMXPath($this->load_html($html));
		$attribute = $attr['attribute'];
		$selector = $this->convert_selector->toXPath($attr['selector']);
		$nodeList = $xpath->query($selector);

		return $nodeList
			->item(0)
			?->attributes
			->getNamedItem($attribute)
			?->nodeValue;
	}

	private function get_source_text($html, $attr)
	{
		$xpath = new \DOMXPath($this->load_html($html));
		$selector = $this->convert_selector->toXPath($attr['selector']);
		$nodeList = $xpath->query($selector);

		return $nodeList->item(0)?->textContent;
	}

	private function get_source_query($html, $attr)
	{
		$xpath = new \DOMXPath($this->load_html($html));
		$selector = $this->convert_selector->toXPath($attr['selector']);
		$nodeList = $xpath->query($selector);
		$result = array();

		foreach ($nodeList as $node) {
			$node_html = $this->save_html($node);

			foreach ($attr['query'] as $key => $query) {
				$query_result = call_user_func_array(
					array($this, 'get_source_' . $query['source']),
					array($node_html, $query)
				);
				array_merge(
					array($key => $query_result),
					$result
				);
			}
		}

		return $result;
	}

	private function load_html($html)
	{
		$this->html->loadHtml($html, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED | LIBXML_NOERROR);

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
