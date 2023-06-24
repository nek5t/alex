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
	public function test_exception_for_unregistered_blocks()
	{
		$this->expectException(UnregisteredBlockException::class);

		/**
		 * Create mock object with protected method
		 * set to public visibility.
		 */
		$mock = new class
		{
			use usesBlockRegistry {
				getBlockType as public;
			}
		};

		$mock->getBlockType(array('blockName' => 'unregistered/block-type'));
	}
}
