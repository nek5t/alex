import React, { ReactElement } from "react"

import { InputTemplate } from '../../lib'

export interface DetailsProps {
    summary: ReactElement | string[],
    details: ReactElement | ReactElement[],
    blockProps: Record<string, any>
}

const Details = ({summary, details, ...blockProps} : DetailsProps) => {
    const detailsContent = details || blockProps.innerBlocks
    const summaryTemplate = () => <summary><h3>{summary}</h3></summary>

    return (
        <details {...blockProps}>
            <summary><InputTemplate template={summaryTemplate} input={summary} /></summary>

            {detailsContent}
        </details>
    ) 
}

export default Details