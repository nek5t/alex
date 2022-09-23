import React from 'react';

import { GutenbergBlock } from '../../lib';

export interface QuoteProps extends GutenbergBlock {
	value: string[];
	citation?: string;
}

const Quote = ({ value, citation, props }: QuoteProps) => {
	return (
		<blockquote {...props}>
			{value.map((__html, i) => (
				<p key={i} dangerouslySetInnerHTML={{ __html }}></p>
			))}

			{citation && <cite>{citation}</cite>}
		</blockquote>
	);
};

export default Quote;
