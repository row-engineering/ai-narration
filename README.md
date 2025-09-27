# AI Narration

A WordPress plugin to enable AI-generated audio of posts, to boost accessibility, engagement, and reach.

## Instalation

1) Install the plugin
- In WordPress admin go to Plugins > Add New > Upload Plugin
- Choose the plugin zip and click Install Now
- Click Activate

2) Confirm it is active
- You should see it listed under Plugins as Active
- A new settings page will appear under Settings or in the left menu

### Minimum setup in Settings

1) Choose the service
- Select your TTS or narration service from the dropdown.
- For now Open AI is the only one supported

2) Add API key
- Paste the key for the chosen service. Save.

3) Choose a Voice
- The most commons ones known for each service are listed.

4) Base directory
- Confirm the base directory where narration assets will be stored or read from. Adjust if your site uses a custom path.

5) Post content selector
- The default selector is `.entry-content` and works with default themes. If your post is a custom template this might have been changed and you will need to provide it here.


That’s all you need for a basic run. Create or view a single post that has a narration index and the script will load.

Full explanations for all settings will be added later.

## Supporting AI Narration

AI Narration is an open source project with its ongoing development made possible entirely by the engineering team at Rest of World - and potentially others in the future. If you'd like to support these efforts, please consider donation which will also help support our global journalism efforts:

- [Donate to Rest of World](https://restofworld.org/donate/).

Rest of World is a 501(c)(3) nonprofit organization and donations in the USA are tax-deductible.

## Development

Pull requests are encouraged and often welcome. [Pick an issue](https://github.com/row-engineering/ai-audio/issues) or a feature and help us out!

To install and work on AI Narration locally:

```bash
cd /path/to/wordpress/wp-content/plugins
git clone https://github.com/row-engineering/ai-narration.git
cd ai-narration
```
### PR Guidelines

We are a small team and reviews may not be timley. We also have a narrow focus for this plugin as it actively runs on our site - mostly on security, stability, and improving existing features. We are not looking to add significant features without consultation. With that in mind:

1. Keep It Focused and Atomic: Small PRs please.
2. Write a Comprehensive PR Description
- What does this PR solve or introduce?
- How did you solve it?
- Any potential side effects or considerations?
3. Meaningful Commit Messages
4. Tests: Include tests or detailed documentation

## Roadmap: 

Our roadmap for this plugin is relatively modest. The core functionality is in place. Our future efforts will be centered around stability, incremental changes, and UX improvements.

We have identified known areas where this plugin falls short and would welcome any help for those.

### Features and Improvements

- Service Support
  - Adding support for additional AI TTS services by extending the current system
- WP Admin
  - A cleaner way to abort, and redo, narration generations.
- Internationalization
  - Any refactoring to better prepare it for language support.
  - Add support for a second language
- Actions and Filters
  - Add new actions or filters at logical/useful points. This may be subjective so use your best judgment.
- Documentation
  - Spelling errors
  - Clearer ways to explain things
  - Addressing any omissions
- Security
  - Please get in touch directly if you find a severe issue and we will prioritize those.

## Hooks

#### ain_script_src

The plugin loads `js/ain-public.js` on single posts that have a narration index. Developers can swap that file using a filter.

Basic override example (from a theme or mu plugin):

```
add_filter('ain_script_src', function ($src, $post) {
  return get_stylesheet_directory_uri() . '/js/ain-narration-custom.js';
}, 10, 2);
```

Return a full URL to your custom script. Return an empty string to skip loading.


#### ain_styles_src

The plugin loads `css/ain-public.css` on single posts that have a narration index. Developers can swap that file using a filter.

Basic override example (from a theme or mu plugin):

```
add_filter('ain_script_src', function ($src, $post) {
  return get_stylesheet_directory_uri() . '/css/ain-narration-custom.css';
}, 10, 2);
```

Return a full URL to your custom stylesheet. Return an empty string to skip loading.
