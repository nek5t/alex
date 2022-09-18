import renderBlocks from "../lib/render-blocks"

const Post = ({post}) => {
    if (!post) return null

    const {blocks} = post

    return (
        <main>{renderBlocks(blocks)}</main>
    )
}

const getStaticPaths = async () => {
    /** TODO: actually, intelligently implement this method */
    return {
        paths: [],
        fallback: true
    }
}

const getStaticProps = async (ctx) => {
    const path = ctx.params?.path.join('/') || '/'
    const endpoint = 'wp-json/alex/v1/posts'
    const url = new URL(endpoint, process.env.WP_URL)
    url.searchParams.set('path', path)
    const res = await fetch(url)
    const post = await res.json()

    return { props: { post } }
}

export { getStaticProps, getStaticPaths }

export default Post