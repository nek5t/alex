import { DOMAttributes, ReactElement } from 'react';

export interface GutenbergBlock {
	innerBlocks?: ReactElement[];
	props?: DOMAttributes<HTMLElement>;
}
