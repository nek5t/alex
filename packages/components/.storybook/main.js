module.exports = {
  "stories": [
    "../stories/**/*.stories.mdx",
    "../stories/**/*.stories.@(js|jsx|ts|tsx)"
  ],
  "addons": [
    "@storybook/addon-links",
    "@storybook/addon-essentials",
    "@storybook/addon-interactions",
    "storybook-css-modules"
  ],
  "framework": "@storybook/react",
  "core": {
    "builder": 'webpack5',
    "disableTelemetry": true,
  },
}