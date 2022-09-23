import React from 'react';
import { default as classnames } from 'classnames';
import { GutenbergBlock, VerticalAlignment } from '../../lib';
import styles from './Columns.module.css';

export interface ColumnsProps extends GutenbergBlock {
	verticalAlignment?: VerticalAlignment;
	isStackedOnMobile?: boolean;
}

const Columns = ({
	verticalAlignment,
	isStackedOnMobile,
	innerBlocks,
	...props
}: ColumnsProps) => (
	<div
		className={classnames({
			[styles.columns]: true,
			[styles[verticalAlignment]]: verticalAlignment,
			[styles.stacked]: isStackedOnMobile,
		})}
		{...props}
	>
		{innerBlocks}
	</div>
);

export default Columns;
