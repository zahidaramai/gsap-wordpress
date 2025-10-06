=== GSAP for WordPress ===
Contributors: zahidaramai
Donate link: https://zahidaramai.com/donate
Tags: animation, gsap, greensock, scrolltrigger, web-animation
Requires at least: 6.7
Tested up to: 6.7
Stable tag: 1.0.2
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete GSAP integration with advanced admin interface, file editor, version control, and 25+ animation libraries for creating stunning WordPress animations.

== Description ==

GSAP for WordPress is a comprehensive plugin that integrates the powerful GreenSock Animation Platform (GSAP) into your WordPress website. This plugin provides easy access to GSAP's animation libraries through a simple admin interface, allowing you to create stunning, smooth animations with minimal effort.

**Key Features:**

* **25+ GSAP Libraries** - Complete integration including ScrollTrigger, MorphSVG, DrawSVG, SplitText, and more
* **Advanced File Editor** - Built-in code editor with syntax highlighting for custom animations
* **Version Control System** - Database-stored version history with restore and compare functionality
* **Selective Loading** - Choose which GSAP libraries to load for optimal performance
* **Performance Optimization** - Minified files, CDN support, conditional loading, and caching
* **Security Features** - Content validation, rate limiting, and capability checks
* **Professional Admin Interface** - Modern, responsive design with tabbed navigation
* **WordPress.org Ready** - Follows all WordPress coding standards and best practices

**Included GSAP Libraries:**

**Free Libraries:**
* GSAP Core - The main animation engine
* CSSPlugin - CSS property animations
* ScrollTrigger - Scroll-driven animations
* TextPlugin - Text animation effects
* Observer - Performance-optimized event handling
* Flip - State-based animations

**Premium Libraries (requires GSAP license):**
* MorphSVG - SVG shape morphing
* DrawSVG - SVG drawing animations
* SplitText - Text splitting and animation
* Draggable - Drag and drop interactions
* InertiaPlugin - Physics-based momentum
* ScrollSmoother - Smooth scrolling effects
* CustomEase - Custom easing curves
* MotionPath - Path-based animations

**Usage Example:**

After activating the plugin and enabling the desired libraries, you can use GSAP in your themes:

```javascript
// Basic animation
gsap.to(".my-element", {duration: 2, x: 100, rotation: 360});

// ScrollTrigger animation
gsap.to(".scroll-element", {
    scrollTrigger: ".scroll-element",
    x: 100,
    duration: 3
});
```

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/gsap-for-wordpress` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->GSAP Settings screen to configure which libraries to load
4. Start using GSAP in your theme files or custom JavaScript

== Frequently Asked Questions ==

= Do I need a GSAP license to use this plugin? =

The free GSAP libraries (Core, CSSPlugin, ScrollTrigger, TextPlugin) can be used without a license. However, premium plugins like MorphSVG, DrawSVG, and SplitText require a valid GSAP license for commercial use.

= How do I use GSAP after installing this plugin? =

Once you've enabled the desired libraries in the settings, you can use GSAP directly in your theme's JavaScript files or inline scripts. The GSAP object will be globally available.

= Can I choose which GSAP libraries to load? =

Yes! The plugin provides granular control over which GSAP libraries are loaded, helping you optimize performance by only loading what you need.

= Is this plugin compatible with other animation libraries? =

GSAP for WordPress is designed to work alongside other libraries. However, for best performance, we recommend using GSAP as your primary animation solution.

== Screenshots ==

1. Admin settings page showing available GSAP libraries
2. Plugin configuration options for performance optimization

== Changelog ==

= 1.0 =
* Initial release
* Complete GSAP library integration
* Admin settings interface
* Support for free and premium GSAP plugins
* Performance optimization options

== Upgrade Notice ==

= 1.0 =
Initial release of GSAP for WordPress.

== License ==

This plugin is licensed under the GPL v2 or later. However, please note that some GSAP plugins included in this package may require separate commercial licensing from GreenSock for commercial use. Please review GreenSock's licensing terms at https://greensock.com/licensing/

== Developer Information ==

**Developer:** Zahid Aramai
**Website:** https://zahidaramai.com
**Version:** 1.0

For support, feature requests, or bug reports, please visit the plugin's support forum or contact the developer directly.