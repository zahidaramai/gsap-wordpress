# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a professional WordPress plugin called "GSAP for WordPress" developed by Zahid Aramai. It provides a comprehensive GSAP (GreenSock Animation Platform) integration with advanced admin interface, file editor, version control system, and security features. The plugin is ready for WordPress Plugin Repository submission.

## Architecture

### Directory Structure
- `gsap-for-wordpress.php` - Main plugin file with WordPress headers and core functionality
- `admin/` - WordPress admin interface files
  - `settings-page.php` - Plugin settings page template
  - `css/admin.css` - Admin interface styles
  - `js/admin.js` - Admin interface JavaScript
- `includes/` - PHP class files and utilities
  - `class-gsap-enqueue.php` - Script enqueuing and dependency management
- `assets/js/src/` - Source JavaScript files for GSAP core and plugins
- `assets/js/minified/` - Minified production versions of all GSAP files
- `assets/css/` - CSS assets (currently empty)
- `languages/` - Translation files directory
- `readme.txt` - WordPress plugin repository readme

### Core Files
- `assets/js/src/gsap-core.js` - Main GSAP core engine (151KB)
- `assets/js/src/index.js` - Primary export file registering CSSPlugin with GSAP core
- `assets/js/src/all.js` - Complete export file including all plugins and utilities
- `assets/js/src/utils/` - Utility modules (matrix operations, path handling, string utilities, etc.)

### Plugin Architecture
The project includes comprehensive GSAP plugins:
- **Core Plugins**: CSSPlugin, ScrollTrigger, Observer, Flip
- **Premium Plugins**: DrawSVGPlugin, MorphSVGPlugin, SplitText, GSDevTools, ScrollSmoother
- **Interaction**: Draggable, InertiaPlugin
- **Effects**: CustomEase, CustomBounce, CustomWiggle, ScrambleTextPlugin
- **Integration**: EaselPlugin, PixiPlugin
- **Physics**: Physics2DPlugin, PhysicsPropsPlugin

### Module System
The codebase uses ES6 modules with:
- Individual plugin exports for tree-shaking
- Combined exports in `all.js` for full GSAP suite
- Minified versions available for production

## Development Notes

### File Organization
- Source files are unminified and include full plugin functionality
- Each plugin is self-contained in its own file
- Utility functions are organized in the `utils/` directory
- TypeScript definitions are available for SplitText plugin

### WordPress Plugin Architecture
This is a complete WordPress plugin with:
- **Singleton Pattern**: Main plugin class using singleton pattern for single instance
- **Hook System**: WordPress activation/deactivation hooks and action/filter integration
- **Settings API**: Custom admin settings page with form handling and validation
- **Script Enqueuing**: Conditional script loading based on user settings
- **Dependency Management**: Automatic resolution of GSAP plugin dependencies
- **Nonce Security**: Proper nonce validation for form submissions

### Plugin Features
- **Selective Loading**: Choose which GSAP libraries to load for performance optimization
- **Admin Interface**: User-friendly settings page in WordPress admin (Settings > GSAP Settings)
- **Dependency Resolution**: Automatic handling of plugin dependencies (e.g., ScrollSmoother requires ScrollTrigger)
- **Performance Options**: Minified vs source files, footer vs header loading
- **Premium Plugin Support**: Integration for premium GSAP plugins with license validation warnings

### Development Commands
No build process required - this is a standard WordPress plugin. For development:
- Install in WordPress `/wp-content/plugins/` directory
- Activate through WordPress admin
- Configure through GSAP > Settings (main menu item)
- Use standard WordPress debugging: `define('WP_DEBUG', true)` in wp-config.php
- Enable GSAP debugging: Plugin automatically respects WP_DEBUG setting

### Key Features Implemented
- **25+ GSAP Libraries**: Complete integration with dependency management
- **Two-Tab Admin Interface**: Settings and Customize (file editor)
- **Version Control System**: Database-stored with restore/compare functionality
- **Professional File Editor**: Syntax highlighting, validation, auto-save
- **Advanced Security**: Content validation, rate limiting, capability checks
- **Performance Optimization**: CDN support, conditional loading, caching
- **WordPress.org Ready**: Complete documentation, proper headers, security compliance