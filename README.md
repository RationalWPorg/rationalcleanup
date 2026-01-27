# RationalCleanup

Clean up legacy WordPress bloat, improve security, and optimize performance. All features are toggleable with opinionated defaults.

## Features

### Head Tags
Remove unnecessary meta tags and links from the document head.

| Option | Default | Description |
|--------|---------|-------------|
| Remove generator meta tag | On | Removes WordPress version from HTML source |
| Remove WLW manifest link | On | Removes Windows Live Writer manifest link |
| Remove RSD link | On | Removes Really Simple Discovery link |
| Remove shortlink | On | Removes shortlink from head |
| Remove REST API link | On | Removes REST API discovery link from head |
| Remove RSS feed links | Off | Removes RSS/Atom feed links |

### Frontend
Remove scripts and styles that most sites don't need.

| Option | Default | Description |
|--------|---------|-------------|
| Remove emoji scripts | On | Removes WordPress emoji detection script and styles |
| Remove jQuery Migrate | On | Removes jQuery Migrate from frontend (keeps jQuery) |
| Remove block library CSS | Off | Removes Gutenberg block library CSS |
| Remove global styles/SVGs | Off | Removes global styles and SVG filters |

### Security
Harden WordPress against common attack vectors.

| Option | Default | Description |
|--------|---------|-------------|
| Disable XML-RPC | On | Completely disables XML-RPC and removes pingback header |
| Prevent user enumeration | On | Blocks author archives and REST API user endpoints for non-logged-in users |
| Obfuscate login errors | On | Shows generic error message on failed login attempts |

### Performance
Reduce unnecessary WordPress overhead.

| Option | Default | Description |
|--------|---------|-------------|
| Disable self-pingbacks | On | Prevents WordPress from pinging itself |
| Throttle Heartbeat API | Off | Reduces Heartbeat API frequency to 60 seconds |
| Extend autosave interval | Off | Extends autosave interval to 120 seconds |

### Features
Disable major WordPress subsystems.

| Option | Default | Description |
|--------|---------|-------------|
| Disable comments | Off | Completely disables comments system, removes menu items |
| Disable block editor | Off | Forces classic editor for all post types |
| Disable REST API for public | Off | Requires authentication for all REST API requests |

### Admin
Declutter the WordPress admin dashboard.

| Option | Default | Description |
|--------|---------|-------------|
| Remove WordPress Events and News | Off | Removes the WordPress Events and News dashboard widget |
| Remove Quick Draft | Off | Removes the Quick Draft dashboard widget |
| Remove At a Glance | Off | Removes the At a Glance dashboard widget |
| Remove Activity | Off | Removes the Activity dashboard widget |
| Remove Site Health Status | Off | Removes the Site Health Status dashboard widget |

## Installation

1. Upload the `rationalcleanup` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under **RationalWP > Cleanup** in the admin menu

## RationalWP Menu

This plugin uses a shared parent menu for all RationalWP plugins. When activated, you'll see a **RationalWP** menu in your admin sidebar (after Settings) containing:

- **RationalWP** - Overview page showing all available RationalWP plugins
- **Cleanup** - This plugin's settings

If you have other RationalWP plugins installed, they will appear as additional submenus under the same parent.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Development

### Running Tests

The plugin includes a PHPUnit test suite with WordPress function mocking via Brain\Monkey.

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run unit tests only
composer test:unit

# Run tests with coverage report (requires Xdebug)
composer test:coverage
```

### Test Structure

```
tests/
├── bootstrap.php       # WordPress function mocks
├── TestCase.php        # Base test class with helpers
└── unit/
    ├── OptionsTest.php     # Tests for options and defaults
    ├── FiltersTest.php     # Tests for filter callbacks
    └── SecurityTest.php    # Tests for security features
```

## License

GPL v2 or later
