import React from 'react'

export interface QuoteProps {
    value: string[],
    citation?: string
}

const Quote = ({value,citation} : QuoteProps) => {
    return (
        <blockquote>
            {value.map((__html, i) => <p key={i} dangerouslySetInnerHTML={{__html}}></p>)}

            {citation && (<cite>{citation}</cite>)}
        </blockquote>
    )
}

export default Quote