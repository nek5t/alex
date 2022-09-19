export type Post = {
	ID: Number;
	post_author: Number;
	post_date: Date;
	post_date_gmt: Date;
	post_content: String;
	post_title: String;
	post_excerpt: String;
	post_status: String;
	comment_status: String;
	ping_status: String;
	post_password: String;
	post_name: String;
	to_ping: String;
	pinged: String;
	post_modified: Date;
	post_modified_gmt: Date;
	post_content_filtered: String;
	post_parent: Number;
	guid: URL;
	menu_order: Number;
	post_type: String;
	post_mime_type: String;
	comment_count: Number;
	filter: String;
	blocks: Array<GutenbergBlock>;
};

export interface GutenbergBlock {
	blockName: String;
	attrs: Record<string, any>;
	innerBlocks: Array<GutenbergBlock>;
}
