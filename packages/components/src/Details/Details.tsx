import React, { ReactElement } from 'react';

import { GutenbergBlock, InputTemplate, TemplateFunction } from '../../lib';
import { default as styles } from './Details.module.css';

export interface DetailsProps extends GutenbergBlock {
	asDiv?: boolean;
	summary: ReactElement | string[];
	details: ReactElement | ReactElement[];
}

const Details = ({
	asDiv,
	summary,
	details,
	innerBlocks,
	...props
}: DetailsProps) => {
	const Tag: keyof JSX.IntrinsicElements = asDiv ? `div` : `details`;
	const detailsContent = details || innerBlocks;
	const summaryTemplate: TemplateFunction = () => <h3>{summary}</h3>;

	return (
		<Tag {...props} className={styles.details}>
			<summary>
				<InputTemplate template={summaryTemplate} input={summary} />
			</summary>

			{detailsContent}
		</Tag>
	);
};

export default Details;
