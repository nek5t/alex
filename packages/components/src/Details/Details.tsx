import React from "react"

export interface DetailsProps {
    renderSummary: Function,
    renderDetails: Function
}

const Details = ({renderSummary, renderDetails} : DetailsProps) => {
    const summary = renderSummary()
    const details = renderDetails()

    return <details><summary>{summary}</summary>{details}</details>
}

export default Details