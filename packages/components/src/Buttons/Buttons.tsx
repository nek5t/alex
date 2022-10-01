import { GutenbergBlock } from '../../lib';

export interface ButtonsProps extends GutenbergBlock {}

const Buttons = ({ innerBlocks }: ButtonsProps) => {
	return <div>{innerBlocks}</div>;
};

export default Buttons;
