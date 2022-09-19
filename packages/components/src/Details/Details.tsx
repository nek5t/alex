import React, { ReactElement } from 'react';

import { InputTemplate } from '../../lib';
import { details as styles } from './Details.module.css';

export interface DetailsProps {
	summary: ReactElement | string[];
	details: ReactElement | ReactElement[];
	blockProps?: Record<string, any>;
}

const Details = ({ summary, details, ...props }: DetailsProps) => {
	const { innerBlocks, ...blockProps } = props;
	const detailsContent = details || innerBlocks;
	const summaryTemplate = () => <h3>{summary}</h3>;

	return (
		<details {...blockProps} className={styles}>
			<summary>
				<InputTemplate template={summaryTemplate} input={summary} />
			</summary>

			{detailsContent}
		</details>
	);
};

export default Details;
