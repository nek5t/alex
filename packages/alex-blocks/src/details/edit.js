/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { InnerBlocks, RichText, useBlockProps } from '@wordpress/block-editor';

import { Details } from '@alex/components';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @param {Object}   props
 * @param {Object}   props.attributes
 * @param {Function} props.setAttributes
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const props = useBlockProps();
	props.asDiv = true;

	props.summary = (
		<RichText
			tagName="h3"
			value={attributes.summary}
			onChange={(summary) => setAttributes({ summary })}
		/>
	);

	props.details = <InnerBlocks />;

	return <Details {...props} />;
}
