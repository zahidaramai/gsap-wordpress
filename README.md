# GSAP for WordPress

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/Version-1.0.0-brightgreen.svg)](https://github.com/zahidaramai/gsap-wordpress)

A comprehensive WordPress plugin that integrates the powerful **GreenSock Animation Platform (GSAP)** into your WordPress website with an advanced admin interface, professional code editor with syntax highlighting, and complete version control system.

## ✨ What's New in v1.0.0

🎉 **Full Production Release** - Complete plugin with all features implemented:

- ✅ **WordPress-Style File Editor** - Professional code editor similar to WordPress plugin/theme editors
- ✅ **Two Editable Files** - `global.js` and `animation.css` with helpful examples and comments
- ✅ **Version Control Sidebar** - Quick access to last 5 versions with create, view, restore actions
- ✅ **Frontend Console Logging** - See activated libraries in browser console with emoji indicators
- ✅ **Friendly Welcome Message** - Pastel-colored instruction box with clear guidance
- ✅ **Auto-Save Every 3 Seconds** - Never lose your work with automatic background saving
- ✅ **Complete Security** - Rate limiting, content validation, and security event logging
- ✅ **Database Version Storage** - All versions saved in WordPress database with auto-cleanup

## 🚀 Features

### Core Functionality
- **Complete GSAP Integration** - Access to 25+ GSAP libraries and plugins
- **Selective Loading** - Choose which GSAP libraries to load for optimal performance
- **Smart Dependency Management** - Automatic resolution of plugin dependencies
- **CDN Support** - Option to load free libraries from CDN for better performance
- **Console Logging** - Real-time frontend console feedback showing activated libraries

### Advanced Admin Interface
- **Two-Tab Navigation** - Settings and Customize tabs for organized management
- **Friendly Instructions** - Welcoming pastel-colored instruction box with helpful guidance
- **Professional UI** - WordPress admin design standards with responsive layout
- **Help Documentation** - Contextual help tabs and comprehensive guidance
- **Real-time Feedback** - Loading states and success/error notifications

### WordPress-Style File Editor
- **Professional Code Editor** - Full-featured editor similar to WordPress plugin/theme editors
- **Two User-Editable Files**:
  - `assets/js/global.js` - Custom GSAP animations with example code
  - `assets/css/animation.css` - Animation styles with helper classes
- **Syntax Highlighting** - Code highlighting for JavaScript and CSS
- **Auto-Save** - Automatic saving every 3 seconds with manual save option
- **Code Validation** - Real-time syntax checking and error detection
- **Code Formatting** - One-click code beautification

### Version Control System
- **Complete Version History** - Track all changes to your animation files
- **Version Sidebar** - Quick access to last 5 versions in the editor
- **Version Management**:
  - Create versions with custom comments
  - View version content in modal
  - Restore any previous version with auto-backup
  - Delete unwanted versions
  - Compare versions with diff viewer
- **Database Storage** - All versions stored securely in WordPress database
- **Export/Import** - Backup and restore version history

### Security & Performance
- **Security-First Design** - Comprehensive validation and sanitization
- **Content Validation** - Prevents malicious code injection
- **Rate Limiting** - Protects against abuse of AJAX endpoints (30 saves per hour)
- **File Path Security** - Protection against directory traversal attacks
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

### 1. Activate the Plugin
1. Upload and activate the plugin in WordPress
2. Navigate to **GSAP** in your WordPress admin menu
3. You'll see a friendly welcome message with instructions

### 2. Configure Libraries (Settings Tab)
Navigate to **GSAP > Settings** and select the libraries you need:

**Core Libraries (Always Recommended):**
- ✅ GSAP Core - The main animation engine (required)
- ✅ CSS Plugin - Enables CSS property animations (recommended)

**Free Plugins:**
- ScrollTrigger - Scroll-driven animations
- Observer - Performance-optimized event handling
- Flip - State-based animations
- TextPlugin - Text animation effects

**Premium Plugins (Requires GSAP License):**
- DrawSVG - SVG drawing animations
- MorphSVG - SVG shape morphing
- SplitText - Text splitting and animation
- And 15+ more premium plugins...

### 3. Edit Custom Animations (Customize Tab)
Click the **Customize** tab to access the WordPress-style file editor:

**Two Files Available for Editing:**

#### 📄 `global.js` - Custom GSAP Animations
Professional code editor with examples:
```javascript
// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {

    // Basic fade-in animation
    gsap.from('.fade-in', {
        duration: 1,
        opacity: 0,
        y: 30,
        stagger: 0.2,
        ease: 'power2.out'
    });

    // ScrollTrigger animation (if enabled)
    if (typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);

        gsap.from('.scroll-animate', {
            scrollTrigger: {
                trigger: '.scroll-animate',
                start: 'top 80%',
                toggleActions: 'play none none reverse'
            },
            duration: 1,
            opacity: 0,
            y: 50
        });
    }
});
```

#### 🎨 `animation.css` - Animation Styles
Add custom CSS for your animations:
```css
/* Base animation classes */
.fade-in {
    opacity: 0;
    transform: translateY(30px);
    will-change: opacity, transform;
}

.scroll-animate {
    opacity: 0;
    transform: translateY(50px);
}

/* GPU acceleration helper */
.gsap-3d {
    transform: translate3d(0, 0, 0);
    backface-visibility: hidden;
}
```

### 4. Version Control
- Click **Create Version** to save a snapshot with a comment
- View version history in the sidebar (last 5 versions)
- Click **View** to see version content
- Click **Restore** to revert to a previous version (auto-creates backup)
- Click **View All Versions** to see complete history

### 5. Verify Activation
Open your browser console (F12) on the frontend to see:
```
🎬 GSAP for WordPress v1.0.0 loaded successfully!
📚 Activated GSAP Libraries:
  ✅ gsap_core (gsap.min.js)
  ✅ scroll_trigger (ScrollTrigger.min.js)
🔄 ScrollTrigger auto-refresh enabled
🚀 GSAP is ready for animations!
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

### WordPress-Style File Editor
The Customize tab provides a professional file editor interface similar to WordPress plugin/theme editors:

**Left Sidebar:**
- **File Tree** - Switch between `global.js` and `animation.css`
- **Version History** - Last 5 versions with dates and comments
- **Quick Actions** - View, restore, or create new versions

**Main Editor Area:**
- **Code Editor** - Full-featured textarea with monospace font
- **Toolbar** - File name, description, and action buttons
- **Status Bar** - File type, cursor position, save status

**Editor Features:**
- ✅ **Auto-Save** - Automatic saving every 3 seconds
- ✅ **Manual Save** - Ctrl+S or Save File button
- ✅ **Code Validation** - Check syntax before saving
- ✅ **Code Formatting** - Auto-indent and beautify code
- ✅ **Reset File** - Restore to default template
- ✅ **Keyboard Shortcuts** - Ctrl+S (save), Ctrl+Z (undo), Ctrl+Y (redo)

**Version Control Features:**
- ✅ **Create Version** - Save snapshots with custom comments
- ✅ **View Version** - Preview version content in modal
- ✅ **Restore Version** - Revert to any previous version (auto-creates backup first)
- ✅ **Delete Version** - Remove unwanted versions
- ✅ **Compare Versions** - See line-by-line differences (diff viewer)
- ✅ **Export Versions** - Download version history as JSON
- ✅ **Database Storage** - All versions stored in `wp_gsap_versions` table
- ✅ **Auto Cleanup** - Keeps last 50 versions per file, deletes versions older than 90 days

## 🛡️ Security Features

### Content Validation
- **Malicious Code Detection** - Prevents harmful script injection (eval, Function, XHR, etc.)
- **File Path Validation** - Protects against directory traversal attacks
- **Rate Limiting** - 30 file saves per hour per user to prevent abuse
- **User Permission Checks** - Requires `edit_themes` capability for file editing
- **Nonce Verification** - All AJAX requests validated with WordPress nonces

### Safe File Operations
- **Atomic File Writes** - Prevents corruption during saves using `file_put_contents()`
- **Automatic Backups** - Creates version backup before restoring previous versions
- **File Size Limits** - Maximum 1MB per file to prevent oversized uploads
- **Extension Validation** - Only allows `.js` and `.css` files
- **Path Sanitization** - Strips `..`, `\` and other dangerous characters
- **Content Sanitization** - Removes null bytes, normalizes line endings, strips BOM

### Version Control Security
- **Database Storage** - Versions stored in WordPress database, not filesystem
- **User Tracking** - Records user ID and timestamp for each version
- **Security Logging** - Logs all file saves, version creates, restores, and deletions
- **IP Address Logging** - Tracks client IP for security auditing (when WP_DEBUG enabled)

## 🎛️ Plugin Architecture

### Class Structure
```
GSAP_For_WordPress (Main Class)
├── GSAP_WP_Admin (Admin Interface)
│   ├── Two-tab navigation (Settings/Customize)
│   ├── Friendly instruction box
│   └── Help system integration
├── GSAP_WP_Settings (Settings Management)
│   ├── Library configuration
│   ├── Performance settings
│   └── Conditional loading options
├── GSAP_WP_File_Editor (WordPress-Style Code Editor)
│   ├── File tree navigation (global.js, animation.css)
│   ├── Version history sidebar
│   ├── Professional code editor interface
│   ├── Toolbar with actions (Save, Validate, Format, Reset)
│   └── Modal dialogs for versions and import/export
├── GSAP_WP_Version_Control (Version Control UI)
│   ├── Version display panel
│   ├── Create/View/Restore/Delete version handlers
│   ├── Diff viewer for comparing versions
│   └── Export/Import functionality
├── GSAP_WP_Version_Manager (Version Database Management)
│   ├── Create and store versions in database
│   ├── Version retrieval and comparison
│   ├── Auto-cleanup of old versions
│   └── Version statistics
├── GSAP_WP_GSAP_Loader (Script Loading)
│   ├── Conditional library loading
│   ├── Dependency resolution
│   ├── Custom file enqueuing (global.js, animation.css)
│   └── Frontend console logging
├── GSAP_WP_File_Manager (File Operations)
│   ├── Safe file reading/writing
│   ├── Backup creation
│   └── File validation
└── GSAP_WP_Security (Security Layer)
    ├── Content validation
    ├── Rate limiting
    ├── User capability checks
    └── Security event logging
```

### Database Tables
- **`wp_gsap_versions`** - Version control history with complete file snapshots
  - Fields: id, file_path, content, version_number, user_comment, created_by, created_at
  - Max 50 versions per file
  - Auto-cleanup after 90 days
- Plugin options stored in `wp_options` table:
  - `gsap_wp_settings` - Library and performance settings
  - `gsap_wp_custom_js` - Inline JavaScript snippets
  - `gsap_wp_custom_css` - Inline CSS snippets
- Custom editable files stored in `wp-content/plugins/gsap-wordpress/assets/`:
  - `assets/js/global.js`
  - `assets/css/animation.css`

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

**Important:** This plugin includes GSAP files for convenience, but you are responsible for ensuring you have the appropriate licenses for your use case. The plugin will warn you when activating premium libraries.

## 🎥 Screenshots

### Settings Tab
Configure which GSAP libraries to load with visual library cards showing free/premium status and file sizes.

### Customize Tab - File Editor
Professional WordPress-style code editor with:
- Left sidebar: File tree and version history
- Main area: Code editor with toolbar and status bar
- Syntax highlighting and auto-save
- Version control integration

### Frontend Console
Browser console showing activated libraries:
```
🎬 GSAP for WordPress v1.0.0 loaded successfully!
📚 Activated GSAP Libraries:
  ✅ gsap_core (gsap.min.js)
  ✅ css_plugin (CSSPlugin.min.js)
  ✅ scroll_trigger (ScrollTrigger.min.js)
🔄 ScrollTrigger auto-refresh enabled
🚀 GSAP is ready for animations!
```

### Version History
- Create versions with custom comments
- View version content in modal
- Restore previous versions with one click
- See who created each version and when

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

### Version 1.0.0 (Current)
- 🎉 Initial release
- ✅ Complete GSAP library integration (25+ libraries)
- ✅ Advanced admin interface with two-tab navigation
- ✅ **WordPress-style file editor** with professional code editing experience
- ✅ **Two user-editable files**: `global.js` and `animation.css` with helpful examples
- ✅ **Complete version control system** with database storage
- ✅ **Version history sidebar** showing last 5 versions with quick actions
- ✅ **Frontend console logging** showing activated libraries with emoji indicators
- ✅ **Friendly instruction box** with pastel background and helpful guidance
- ✅ Security-first architecture with comprehensive validation
- ✅ **Rate limiting** (30 saves per hour) and security event logging
- ✅ Performance optimization with auto-save every 3 seconds
- ✅ **Code validation and formatting** built into the editor
- ✅ **Auto-cleanup** of old versions (50 max per file, 90-day retention)
- ✅ WordPress Plugin Repository ready
- ✅ Full internationalization support (translation-ready)

## 🏆 Credits

**Developed by:** [Zahid Aramai](https://zahidaramai.com)
**GSAP by:** [GreenSock](https://greensock.com)
**WordPress Integration:** Custom architecture following WordPress best practices

## 📜 License

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

---

**Made with ❤️ for the WordPress community**