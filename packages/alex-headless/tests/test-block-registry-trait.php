<?php

/**
 * Tests for the trait usesBlockRegistry
 *
 * @package alex/alex-headless
 */

use Alex\Headless\Parser\usesBlockRegistry;
use Alex\Headless\Parser\UnregisteredBlockException;

class UsesBlockRegistryTraitTest extends WP_UnitTestCase
{
	protected $mock;

	public function setUp() : void
	{
		/**
		 * Create mock object that uses our trait,
		 * with visibility of the method we would
		 * like to test set to public.
		 */
		$this->mock = new class
		{
			use usesBlockRegistry {
				getBlockType as public;
			}
		};
	}

	public function test_exception_for_unregistered_blocks()
	{
		$this->expectException(UnregisteredBlockException::class);

		$this->mock->getBlockType(array('blockName' => 'unregistered/block-type'));
	}

	public function test_returns_registered_block()
	{
		$block = $this->mock->getBlockType(array(
			'blockName' => 'core/button'
		));

		$this->assertInstanceOf(WP_Block_Type::class, $block);
	}
}
