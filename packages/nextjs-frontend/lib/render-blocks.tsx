import React from 'react';
import mapping from '../constants/block-mapping';
import { GutenbergBlock } from '../lib';

const renderBlocks = (blocks: Array<GutenbergBlock>) => {
	if (undefined === blocks) return [];

	return blocks
		.filter((b) => b.blockName)
		.filter((b) => mapping.get(b.blockName))
		.map((b, i) => {
			const { blockName, attrs, innerBlocks, ...blockProps } = b;
			const Component = mapping.get(blockName);

			const renderedInnerBlocks = renderBlocks(innerBlocks);

			const props = {
				...attrs,
				innerBlocks: renderedInnerBlocks,
				...blockProps,
			};

			return <Component key={i} {...props} />;
		});
};

export default renderBlocks;
