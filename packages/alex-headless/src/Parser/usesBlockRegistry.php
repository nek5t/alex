<?php
/**
 * Trait definition for parsers that use block registry
 */
namespace Alex\Headless\Parser;
use Alex\Headless\Parser\UnregisteredBlockException;

/**
 * Shared methods for parsers that need to use the block registry
 */
trait usesBlockRegistry
{
	/**
	 * Return a block name from an array of block properties
	 *
	 * @param array $block
	 * @return string
	 */
	protected function getBlockName( array $block ) : string
	{
		return $block['blockName'] ?? '';
	}

	/**
	 * Return a block type object from the block type registry
	 *
	 * @param array $block
	 * @return \WP_Block_Type|null
	 */
	protected function getBlockType(array $block) : \WP_Block_Type
	{
		$block_name = $this->getBlockName($block);

		if ( empty( $block_name ) ) {
			throw new UnregisteredBlockException('Block name is empty.');
		}

		$block_type = \WP_Block_Type_Registry::get_instance()->get_registered($block_name);

		if (null === $block_type) {
			throw new UnregisteredBlockException("$block_name is not a registered block type.");
		}
	}
}
