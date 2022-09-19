/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  swcMinify: true,
  experimental: {
    externalDir: true,
  },
}

const withTranspiledModules = require('next-transpile-modules')(['@alex/components'])

module.exports = withTranspiledModules(nextConfig)
