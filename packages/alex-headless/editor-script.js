(function(wp, alex) {
    wp.domReady(function() {
        const allRegistered = wp.blocks.getBlockTypes()

        for (let block of allRegistered) {
            if (false === alex.allowedBlocks.includes(block.name)) {
                wp.blocks.unregisterBlockType(block.name)
            }
        }
    })
})(wp,alexhless)