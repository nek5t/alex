import React from 'react'
import mapping from '../constants/block-mapping'

const renderBlocks = (blocks) => {
    return blocks
        .filter(b => b.blockName)
        .filter(b => mapping.get(b.blockName))
        .map((b,i) => {
        const { blockName, attrs, ...blockProps } = b
        const Component = mapping.get(blockName)

        blockProps.innerBlocks = renderBlocks(blockProps.innerBlocks)

        const props = { ...attrs, ...blockProps }

        return <Component key={i} {...props} />
    })
}

export default renderBlocks