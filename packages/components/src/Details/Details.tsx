import React, { ReactElement } from 'react';

import { InputTemplate, TemplateFunction } from '../../lib';
import { default as styles } from './Details.module.css';

export interface DetailsProps {
	summary: ReactElement | string[];
	details: ReactElement | ReactElement[];
	innerBlocks?: ReactElement[];
	props?: Record<string, any>;
}

const Details = ({ summary, details, innerBlocks, ...props }: DetailsProps) => {
	const detailsContent = details || innerBlocks;
	const summaryTemplate: TemplateFunction = () => <h3>{summary}</h3>;

	return (
		<details {...props} className={styles.details}>
			<summary>
				<InputTemplate template={summaryTemplate} input={summary} />
			</summary>

			{detailsContent}
		</details>
	);
};

export default Details;
