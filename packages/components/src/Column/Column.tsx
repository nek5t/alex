import React from 'react';
import { default as classnames } from 'classnames';
import { GutenbergBlock, VerticalAlignment } from '../../lib';
import styles from './Column.module.css';

export interface ColumnProps extends GutenbergBlock {
	verticalAlignment?: VerticalAlignment;
	width?: string;
}

const Column = ({
	verticalAlignment,
	width,
	innerBlocks,
	...props
}: ColumnProps) => {
	const inlineStyles: React.CSSProperties = {};

	if (width) {
		inlineStyles.flexBasis = width;
	}

	return (
		<div
			className={classnames({
				[styles.column]: true,
				[styles.verticalAlignment]: verticalAlignment,
			})}
			style={inlineStyles}
			{...props}
		>
			{innerBlocks}
		</div>
	);
};

export default Column;
