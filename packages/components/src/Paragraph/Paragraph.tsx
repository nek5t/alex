import React from 'react';
import { GutenbergBlock } from '../../lib';

export interface ParagraphProps extends GutenbergBlock {
	content: string;
}

const Paragraph = ({ content: __html, props }: ParagraphProps) => {
	return <p {...props} dangerouslySetInnerHTML={{ __html }} />;
};

export default Paragraph;
