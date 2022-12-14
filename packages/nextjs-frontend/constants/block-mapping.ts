import {
	Column,
	Columns,
	Paragraph,
	Image,
	List,
	Quote,
	Details,
} from '@alex/components';

const blockMap = new Map();

blockMap.set('core/paragraph', Paragraph);
blockMap.set('core/image', Image);
blockMap.set('core/list', List);
blockMap.set('core/quote', Quote);
blockMap.set('core/columns', Columns);
blockMap.set('core/column', Column);
blockMap.set('alexblocks/details', Details);

export default blockMap;
