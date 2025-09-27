# AI Narration

A WordPress plugin to enable AI-generated audio of posts, to boost accessibility, engagement, and reach.

## Supporting AI Narration

AI Narration is an open source project with its ongoing development made possible entirely by the engineering team at Rest of World - and potentially others in the future. If you'd like to support these efforts, please consider donation which will also help support our global journalism efforts:

- [Donate to Rest of World](https://restofworld.org/donate/).

Rest of World is a 501(c)(3) nonprofit organization and donations in the USA are tax-deductible.

### Development

Pull requests are encouraged and often welcome. [Pick an issue](https://github.com/row-engineering/ai-audio/issues) or a feature and help us out!

To install and work on AI Narration locally:

```bash
cd /path/to/wordpress/wp-content/plugins
git clone https://github.com/row-engineering/ai-narration.git
cd ai-narration
```

Go to the Plugins page in the WordPress dashboard to activate.

## Questions

Questions are one way to get answers.

## Hooks

### ain_script_src

The plugin loads `js/ain-public.js` on single posts that have a narration index. Developers can swap that file using a filter.

Basic override example (from a theme or mu plugin):

```
add_filter('ain_script_src', function ($src, $post) {
    return get_stylesheet_directory_uri() . '/js/ain-narration-custom.js';
}, 10, 2);
```

Return a full URL to your custom script. Return an empty string to skip loading.