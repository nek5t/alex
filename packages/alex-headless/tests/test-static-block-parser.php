<?php
/**
 * Tests for StaticBlockParser implementation
 *
 * @package alex/alex-headless
 */
use Alex\Headless\Parser\StaticBlockParser;
use Symfony\Component\CssSelector\CssSelectorConverter;
/**
 * StaticBlockParser test cases.
 */
class StaticBlockParserTest extends WP_UnitTestCase {
	protected $parser;

	public function setUp() : void
	{
		parent::setUp();
		$this->parser = new StaticBlockParser(
			new \DOMDocument(),
			new CssSelectorConverter()
		);
	}

	/**
	 * A single example test.
	 */
	public function test_invalid_blocks_return_empty() {
		$block = [
			'blockName' => 'unregistered/block-type'
		];

		$this->assertEmpty($this->parser->parse($block));

		$block['blockName'] = '';

		$this->assertEmpty($this->parser->parse($block));
	}
}
