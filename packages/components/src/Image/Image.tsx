import React from 'react';
import { GutenbergBlock } from '../../lib';
import styles from './Image.module.css';

interface ImageProps extends GutenbergBlock {
	align: string;
	sizeSlug: string;
	alt: string;
	url: string;
	caption: Array<HTMLElement>;
}

const Image = ({ url, alt }: ImageProps) => {
	return <img className={styles.img} alt={alt} src={url} />;
};

export default Image;
