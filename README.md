# GSAP for WordPress

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](LICENSE)

A comprehensive WordPress plugin that integrates the powerful **GreenSock Animation Platform (GSAP)** into your WordPress website with an advanced admin interface, file editor, and version control system.

## 🚀 Features

### Core Functionality
- **Complete GSAP Integration** - Access to 25+ GSAP libraries and plugins
- **Selective Loading** - Choose which GSAP libraries to load for optimal performance
- **Smart Dependency Management** - Automatic resolution of plugin dependencies
- **CDN Support** - Option to load free libraries from CDN for better performance

### Advanced Admin Interface
- **Two-Tab Navigation** - Settings and Customize tabs for organized management
- **Professional UI** - WordPress admin design standards with responsive layout
- **Help Documentation** - Contextual help tabs and comprehensive guidance
- **Real-time Feedback** - Loading states and success/error notifications

### File Editor & Version Control
- **Syntax-Highlighted Editor** - Professional code editor for JavaScript and CSS
- **Version Control System** - Track, restore, and manage file versions with comments
- **File Management** - Safe file operations with atomic writes and backups
- **Import/Export** - Backup and restore functionality for easy migration

### Security & Performance
- **Security-First Design** - Comprehensive validation and sanitization
- **Content Validation** - Prevents malicious code injection
- **Rate Limiting** - Protects against abuse of AJAX endpoints
- **Performance Optimization** - Conditional loading, compression, and caching

## 📋 Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **User Permissions:** `manage_options` for settings, `edit_themes` for file editing

## 🛠 Installation

### From WordPress Admin
1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin

### Manual Installation
1. Extract the plugin folder to `/wp-content/plugins/`
2. Activate through the **Plugins** menu in WordPress
3. Go to **GSAP > Settings** to configure

## 🎯 Quick Start

### 1. Configure Libraries
Navigate to **GSAP > Settings** and select the libraries you need:

**Core Libraries (Always Recommended):**
- ✅ GSAP Core - The main animation engine
- ✅ CSS Plugin - Enables CSS property animations

**Free Plugins:**
- ScrollTrigger - Scroll-driven animations
- Observer - Performance-optimized event handling
- Flip - State-based animations
- TextPlugin - Text animation effects

**Premium Plugins (Requires GSAP License):**
- DrawSVG - SVG drawing animations
- MorphSVG - SVG shape morphing
- SplitText - Text splitting and animation
- And many more...

### 2. Performance Settings
- **✅ Use Minified Files** - Smaller file sizes for production
- **✅ Load in Footer** - Better page load performance
- **⚠️ CDN Loading** - For free libraries only
- **✅ Cache Busting** - Ensures updated files are loaded

### 3. Custom Animations
Use the **Customize** tab to write custom animations:

**Global JavaScript (global.js):**
```javascript
// Basic animation
gsap.to(".my-element", {
    duration: 2,
    x: 100,
    rotation: 360,
    ease: "power2.out"
});

// ScrollTrigger animation (if enabled)
gsap.registerPlugin(ScrollTrigger);
gsap.to(".scroll-element", {
    scrollTrigger: ".scroll-element",
    x: 100,
    duration: 3
});
```

**Animation Styles (animation.css):**
```css
/* Hide elements that will be animated */
.gsap-fade-in {
    opacity: 0;
}

/* Animation-ready classes */
.gsap-element {
    transform-origin: center center;
}
```

## 🎨 Available GSAP Libraries

### Core Libraries
| Library | Size | Description |
|---------|------|-------------|
| GSAP Core | 47KB | Main animation engine (required) |
| CSS Plugin | 12KB | CSS property animations (recommended) |

### Free Plugins
| Library | Size | Description |
|---------|------|-------------|
| ScrollTrigger | 29KB | Scroll-driven animations and effects |
| Observer | 8KB | Performance-optimized event handling |
| Flip | 15KB | State-based animations with transitions |
| TextPlugin | 3KB | Text content animation effects |
| ScrollToPlugin | 4KB | Smooth scrolling to specific elements |

### Premium Plugins
| Library | Size | Description | License Required |
|---------|------|-------------|------------------|
| DrawSVG | 5KB | SVG stroke drawing animations | ✅ |
| MorphSVG | 12KB | SVG shape morphing | ✅ |
| SplitText | 8KB | Text splitting for character/word animation | ✅ |
| ScrollSmoother | 16KB | Ultra-smooth scrolling with effects | ✅ |
| GSDevTools | 22KB | Visual timeline debugging tools | ✅ |
| Draggable | 35KB | Drag-and-drop interactions | ✅ |
| And 10+ more... | | Advanced effects and integrations | ✅ |

## 📚 Usage Examples

### Basic Fade-in Animation
```javascript
// Add this to your theme or use the file editor
document.addEventListener("DOMContentLoaded", function() {
    gsap.from(".fade-in-element", {
        duration: 1,
        opacity: 0,
        y: 20,
        ease: "power2.out"
    });
});
```

### ScrollTrigger Animation
```javascript
gsap.registerPlugin(ScrollTrigger);

gsap.to(".parallax-element", {
    yPercent: -50,
    ease: "none",
    scrollTrigger: {
        trigger: ".parallax-element",
        start: "top bottom",
        end: "bottom top",
        scrub: true
    }
});
```

### Text Animation with SplitText
```javascript
gsap.registerPlugin(SplitText);

let split = new SplitText(".animated-text", {type: "chars"});
gsap.from(split.chars, {
    duration: 0.8,
    opacity: 0,
    scale: 0,
    y: 80,
    rotationX: 180,
    transformOrigin: "0% 50% -50",
    ease: "back",
    stagger: 0.01
});
```

## 🔧 Advanced Configuration

### Conditional Loading
Load GSAP only on specific pages or post types:

1. Enable **Conditional Loading** in settings
2. Select **Include Pages** or **Post Types**
3. Save settings for optimized performance

### File Editor Features
- **Syntax Highlighting** - JavaScript and CSS support
- **Line Numbers** - Easy code navigation
- **Auto-save** - Changes saved automatically
- **Version Control** - Track and restore previous versions
- **Validation** - Real-time syntax checking
- **Keyboard Shortcuts** - Professional editor shortcuts

### Version Control
- **Automatic Versioning** - Every save creates a new version
- **Version Comments** - Add descriptions to your changes
- **Quick Restore** - One-click restoration to previous versions
- **Version Comparison** - See differences between versions
- **Export/Import** - Backup and migrate your customizations

## 🛡️ Security Features

### Content Validation
- **Malicious Code Detection** - Prevents harmful script injection
- **File Path Validation** - Protects against directory traversal
- **Rate Limiting** - Prevents abuse of admin functions
- **User Permission Checks** - Proper capability validation

### Safe File Operations
- **Atomic File Writes** - Prevents corruption during saves
- **Automatic Backups** - Creates backups before modifications
- **File Size Limits** - Prevents oversized file uploads
- **Extension Validation** - Only allows .js and .css files

## 🎛️ Plugin Architecture

### Class Structure
```
GSAP_For_WordPress (Main Class)
├── GSAP_WP_Admin (Admin Interface)
├── GSAP_WP_Settings (Settings Management)
├── GSAP_WP_File_Editor (Code Editor)
├── GSAP_WP_Version_Manager (Version Control)
├── GSAP_WP_GSAP_Loader (Script Loading)
├── GSAP_WP_File_Manager (File Operations)
└── GSAP_WP_Security (Security Layer)
```

### Database Tables
- `wp_gsap_versions` - Version control history
- Plugin options stored in `wp_options` table
- Custom files stored in `wp-content/uploads/gsap-wordpress/`

## 🤝 Contributing

We welcome contributions! Please follow these guidelines:

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Commit** your changes: `git commit -m 'Add amazing feature'`
4. **Push** to the branch: `git push origin feature/amazing-feature`
5. **Open** a Pull Request

### Development Setup
```bash
# Clone the repository
git clone https://github.com/zahidaramai/gsap-wordpress.git

# Navigate to the plugin directory
cd gsap-wordpress

# Install in WordPress plugins directory
cp -r . /path/to/wordpress/wp-content/plugins/gsap-wordpress/
```

## 📝 Licensing

### Plugin License
This plugin is licensed under **GPL v2 or later**.

### GSAP Licensing
GSAP libraries included in this plugin have their own licensing:

- **Free Plugins** - No license required for most uses
- **Premium Plugins** - Require a [Club GreenSock](https://greensock.com/club/) membership for commercial use

**Important:** This plugin includes GSAP files for convenience, but you are responsible for ensuring you have the appropriate licenses for your use case.

## 🆘 Support

### Documentation
- **Plugin Documentation** - Available in WordPress admin help tabs
- **GSAP Documentation** - [greensock.com/docs](https://greensock.com/docs/)
- **Video Tutorials** - [greensock.com/learning](https://greensock.com/learning/)

### Community Support
- **WordPress Support Forum** - For general WordPress integration questions
- **GSAP Forums** - [greensock.com/forums](https://greensock.com/forums/) for GSAP-specific questions
- **GitHub Issues** - For plugin bugs and feature requests

### Professional Support
For custom development or professional support, contact [zahidaramai.com](https://zahidaramai.com).

## 📊 Changelog

### Version 1.0.0
- 🎉 Initial release
- ✅ Complete GSAP library integration (25+ libraries)
- ✅ Advanced admin interface with two-tab navigation
- ✅ Professional file editor with syntax highlighting
- ✅ Comprehensive version control system
- ✅ Security-first architecture with validation
- ✅ Performance optimization features
- ✅ WordPress Plugin Repository ready

## 🏆 Credits

**Developed by:** [Zahid Aramai](https://zahidaramai.com)
**GSAP by:** [GreenSock](https://greensock.com)
**WordPress Integration:** Custom architecture following WordPress best practices

## 📜 License

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

---

**Made with ❤️ for the WordPress community**