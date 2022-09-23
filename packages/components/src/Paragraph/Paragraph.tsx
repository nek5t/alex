import React from 'react';
import { GutenbergBlock } from '../../lib';
import styles from './Paragraph.module.css';

export interface ParagraphProps extends GutenbergBlock {
	content: string;
}

const Paragraph = ({ content: __html, props }: ParagraphProps) => {
	return (
		<p
			{...props}
			dangerouslySetInnerHTML={{ __html }}
			className={styles.paragraph}
		/>
	);
};

export default Paragraph;
