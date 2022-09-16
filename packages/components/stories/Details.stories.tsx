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

export const Primary: ComponentStory<typeof Details> = () => <Details renderSummary={() => <h1>Summary</h1>} renderDetails={() => <p>Details</p>} />;