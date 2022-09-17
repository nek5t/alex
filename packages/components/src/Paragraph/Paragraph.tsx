import React from "react"

export interface ParagraphProps {
    content: string
}

const Paragraph = ({content} : ParagraphProps) => {

    return <p>{content}</p>
}

export default Paragraph