import { Paragraph, List, Quote, Details } from '@alex/components';

const blockMap = new Map();

blockMap.set('core/paragraph', Paragraph);
blockMap.set('core/list', List);
blockMap.set('core/quote', Quote);
blockMap.set('alexblocks/details', Details);

export default blockMap;
