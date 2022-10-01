import { GutenbergBlock } from '../../lib';

export type ArchiveLink = {
	href: string;
	title: string;
};

export interface ArchiveProps extends GutenbergBlock {
	displayAsDropdown: boolean;
	showPostCounts: boolean;
	type: 'yearly' | 'monthly' | 'weekly' | 'daily';
	links: Array<ArchiveLink>;
}

const Archive = () => {};

export default Archive;
