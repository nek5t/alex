import React, { ReactElement } from 'react'

export interface InputTemplateProps {
    template?: Function,
    input: ReactElement | string[]
}

const InputTemplate = ({template, input} : InputTemplateProps) => {
    if (React.isValidElement(input)) return input

    if (undefined === template) return null

    return template()
}

export default InputTemplate