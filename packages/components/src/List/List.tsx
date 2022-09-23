import React from 'react';
import { GutenbergBlock } from '../../lib';

export interface ListProps extends GutenbergBlock {
	ordered: boolean;
	values: Array<string>;
}

const List = ({ ordered, values, props }: ListProps) => {
	const Tag: keyof JSX.IntrinsicElements = ordered ? 'ol' : 'ul';

	return (
		<Tag {...props}>
			{values.map((__html, index) => (
				<li key={index} dangerouslySetInnerHTML={{ __html }} />
			))}
		</Tag>
	);
};

export default List;
