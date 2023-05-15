( function ( wp, alex ) {
	wp.domReady( function () {
		const allRegistered = wp.blocks.getBlockTypes();

		for ( const block of allRegistered ) {
			if ( false === alex.allowedBlocks.includes( block.name ) ) {
				wp.blocks.unregisterBlockType( block.name );
			}
		}
	} );
	// eslint-disable-next-line no-undef
} )( wp, alexhless );
