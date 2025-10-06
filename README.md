# AI Narration

A WordPress plugin that converts your posts into audio narrations using AI text-to-speech services, making your content more accessible and engaging for your audience.

This plugin is actively used and maintained by [Rest of World](https://restofworld.org/).

## Overview

**Out of the box:** This plugin creates audio files for your WordPress posts using AI text-to-speech and adds a responsive audio player to each post. Visitors can listen to your content instead of (or alongside) reading it.

**How it works:** The plugin processes your post content (headings and paragraphs) and generates multiple MP3 files that play seamlessly together. Content is split into smaller audio segments to ensure fast loading - your readers don't have to wait for one large file to download before they can start listening.

**Content processing:** Only heading and paragraph blocks are converted to audio. Other elements (images, galleries, etc.) are skipped, with natural pauses added between sections for a smooth listening experience.

### Important Limitations & Requirements

- Requires [OpenAI API key](https://platform.openai.com/docs/guides/text-to-speech) and account - You'll need to sign up with OpenAI and pay for TTS usage
- Currently supports OpenAI only - Other TTS services may be added in future versions
- Manual setup required - You'll need to configure API keys and may need to specify information to identify post content
- Storage space - Audio files are stored locally on your server
- Theme compatibility - May require CSS selector adjustment if your theme uses non-standard post markup

## Key Features

- **TTS** - OpenAI Text-to-Speech integration with multiple voice options
- **Fully functional player** - Responsive audio player that works on all devices
- **Flexible generation** - Create narrations manually or automatically on post publish
- **Bulk Action** - Bulk narration generation for existing content
- **Customizable** - Custom intro/outro messages for each narration
- **Extensibility** - Developer-friendly with hooks and filters for customization
- **Local hosting** - Audio files stored on your server (no third-party dependencies for playback)

## Installation

- In WordPress admin go to _Plugins > Add New > Upload Plugin_
- Choose the plugin zip and click _Install Now_
- Click _Activate_
- _Narrations_ should appear in the menu

### Minimum Configuration

Go to the plugin settings page.

- **Choose service:** Select "OpenAI" (currently the only option)
- **Add API key:** Paste your OpenAI API key and save
- **Select voice:** Choose from available voices
- **Post content selector:** Default is `main .entry-content` - change this if your theme uses different markup

Click _Save Changes_.

We recommend that you only enable _Auto-Generate on Publish_ after manually generating a test narration.

### Additional Configuration

- **Intro & outro text:** Custom text to be read at the beginning or end of each narration. Includes variables for the headline, author and publish date.
- **Intro & outro MP3:** Paths to custom audio files to be played at the beginning or end of each narration.
- **Player placement:** Specify where the player should be inserted, as counted by word-count, number of paragraphs, etc.
- **Learn More link:** A link for an optional 'Learn More' link to appear in the player
- **CDN:** Serve the audio files from your specified CDN

### Eligibility Criteria

You can customize certain criteria for posts to be eligible for AI narration.

- **Post type**
- **Tags:** Posts with any of the listed tags will be excluded
- **Date:** Posts before the given date will be excluded
- **Word Count:** Posts with fewer than the minimum or more than the maximum specified word counts will be excluded

### Requirements

- WordPress 6.0+ (Gutenberg support recommended)
  - Likley works with WP 5+ but untested
- PHP 7.4+
- OpenAI API account with TTS access
- Adequate server storage for audio files

## Development

To install and work on AI Narration locally:

```bash
cd /path/to/wordpress/wp-content/plugins
git clone https://github.com/row-engineering/ai-narration.git
cd ai-narration
```

## Roadmap: 

Our roadmap for this plugin is relatively modest. The core functionality is in place. Our future efforts will be centered around stability, incremental changes, and UX improvements.

We are a small team and reviews may not be timely. We also have a narrow focus for this plugin as it actively runs on our site - mostly on security, stability, and improving existing features. We are not looking to add significant features without consultation.

### Priority areas for contribution:

- Service Support
  - Adding support for additional AI TTS services by extending the current system
- WP Admin
  - A cleaner way to abort, and redo, narration generations
- Internationalization
  - Any refactoring to better prepare it for language support
  - Add support for a second language
- Actions and Filters
  - Add new actions or filters at logical/useful points. This may be subjective so use your best judgment.
- Documentation
  - Spelling errors
  - Clearer ways to explain things
  - Addressing any omissions
- Security
  - Please get in touch directly if you find a severe issue and we will prioritize those.

### Contribution guidelines:

- Keep PRs focused and atomic
- Include comprehensive but relevant descriptions
- Add tests or detailed documentation
- Follow existing code and formatting standards (tabs, not spaces)

Pull requests are encouraged and often welcome. [Pick an issue](https://github.com/row-engineering/ai-narration/issues) or a feature and help us out!

## Technical Details

### Content Processing

- Processes specific Gutenberg blocks: heading, paragraph, and seperator blocks
- Skips multimedia, shortcodes, and all other blocks blocks
- Compatible with Classic Editor paragraph content
- Adds natural pauses between sections using separator elements

### File Management

When narrations are generated, the plugin creates:
- MP3 files: Individual audio segments (`audio_01.mp3`, `audio_02.mp3`, etc.)
- JSON metadata file: `index.json` contains the same data structure as the inline JavaScript (see below)
  - The JSON file can be used for external integrations or caching strategies.
- Directory structure: `/wp-content/narrations/YYYY/post-slug/`

### Data Structure
The plugin inserts inline JavaScript data via a variable in global namespace to each Post that has a narration. This mirrors the data from the JSON file created for each narration. The player relies on this for rendering and playback:

```
<script id='ai-narration-data'>
window.AINarrationData = {
    "id": 88241,
    "title": "My mom and Dr. DeepSeek",
    "date": "2025-09-02 06:00:00",
    "url": "https:\/\/restofworld.org\/2025\/ai-chatbot-china-sick\/",
    "slug": "ai-chatbot-china-sick",
    "authors": [ "Viola Zhou" ],
    "audio": {
		    "service": "OpenAI TTS",
		    "model": "tts-1",
		    "voice": "shimmer",
		    "created": "1757088590.149598",
		    "total": 8,
		    "duration": [ 203, 247, 257, 240, 246, 245, 235, 41 ],
		    "tracks": [
            "\/wp-content\/narrations\/2025\/ai-chatbot-china-sick\/audio_01.mp3",
            // ... additional tracks
            "\/wp-content\/narrations\/2025\/ai-chatbot-china-sick\/audio_08.mp3"
        ]
    },
    "config": {
        "cdn": "",
        "link": "\/about\/ai-narrations\/",
        "selector": "main .entry-content",
        "position": [false, 0]
    }
}
</script>
```
Example story: [My mom and Dr. DeepSeek](https://restofworld.org/2025/ai-chatbot-china-sick/)

## Developer Customization

`ain_script_src`

Override the player JavaScript file:
```
add_filter('ain_script_src', function ($src, $post) {
  return get_stylesheet_directory_uri() . '/js/narration-script.js';
}, 10, 2);
```

`ain_script_version`

Append a query parameter to your custom JS file to bust browser cache after an update:
```
add_filter('ain_script_version', function ($version, $post) {
	return $version . random_cache_param();
}, 10, 2);
```

`ain_styles_src`

Override the player CSS file:
```
add_filter('ain_styles_src', function ($src, $post) {
  return get_stylesheet_directory_uri() . '/css/narration-styles.css';
}, 10, 2);
```

`ain_styles_version`

Append a query parameter to your custom CSS file to bust browser cache after an update:
```
add_filter('ain_styles_version', function ($version, $post) {
	return $version . random_cache_param();
}, 10, 2);
```

`narration_request`

Add your own filters to disqualify a post from AI narration.
```
add_filter('narration_request', function ( $data = array() ) {
	$post_id = $data['id'];
	$article_flags = get_article_flags($post_id);

	// If post has force-include-ai-narration flag, generate despite other exclusion params
	if ( in_array('force-include-ai-narration', $article_flags) ) {
		error_log("Post $post_id has the force-include-ai-narration flag.");
		return $data;
	}

	// Must not have exclude-from-ai-narration flag
	if ( in_array('exclude-from-ai-narration', $article_flags) ) {
		error_log("Post $post_id has the exclude-from-ai-narration flag.");
		return false;
	}

	// Must use Regular, Medium or Full template
	$template = get_field('row_post_template', $post_id);
	$accepted_templates = array('regular', 'medium', 'full');
	if ( !in_array( $template, $accepted_templates ) ) {
		error_log("Post $post_id is not using one of the supported templates: Regular, Medium or Full.");
		return false;
	}

	return $data;
}, 10, 1);
```

## Supporting AI Narration

AI Narration is an open source project with its ongoing development made possible entirely by the engineering team at [Rest of World](https://restofworld.org/) - and potentially others in the future. If you'd like to support these efforts, please consider donation which will also help support our global journalism efforts:

- [Donate to Rest of World](https://restofworld.org/donate/).

Rest of World is a 501(c)(3) nonprofit organization and donations in the USA are tax-deductible.

## Change Log

[View the change log](https://github.com/row-engineering/ai-narration/blob/master/CHANGELOG.md).
Return a full URL to your custom stylesheet. Return an empty string to skip loading.

