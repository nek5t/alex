import React from "react"

import { ComponentStory, ComponentMeta } from '@storybook/react';

import { Details } from '../src';

export default {
  /* 👇 The title prop is optional.
  * See https://storybook.js.org/docs/react/configure/overview#configure-story-loading
  * to learn how to generate automatic titles
  */
  title: 'Details',
  component: Details,
} as ComponentMeta<typeof Details>;

const props = {
  summary: <h3>Cool Summary</h3>,
  details: <p>Cool Details</p>
}

export const Primary: ComponentStory<typeof Details> = () => <Details {...props} />;