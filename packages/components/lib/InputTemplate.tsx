import React, { ReactElement } from 'react';

export type TemplateFunction = () => ReactElement;

export interface InputTemplateProps {
	template?: TemplateFunction;
	input: ReactElement | Array<string>;
}

const InputTemplate = ({ template, input }: InputTemplateProps) => {
	if (React.isValidElement(input)) return input;

	if (undefined === template) return null;

	return template();
};

export default InputTemplate;
