import { ReactElement } from 'react';

export interface GutenbergBlock {
	innerBlocks?: ReactElement[];
	props?: Record<string, any>;
}
