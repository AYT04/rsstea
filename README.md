# rssTea - YouTube-Style RSS & Podcast Web Reader

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg)](https://www.php.net/)

**rssTea** is a lightweight, web-based RSS and podcast reader featured with a modern, responsive YouTube-inspired interface. It parses RSS and Atom feeds, extracts metadata (including media enclosures, thumbnails, and podcasts), and compiles them into a fast, static web application.

![rssTea Screenshot](public/512.png)

---

## Key Features

- **YouTube-Inspired UI**: Clean, grid-based card layout for an intuitive browsing experience.
- **Podcast & Audio Playback**: Built-in floating audio player with playback speed controls (+30s skip, speed adjustment up to 2x), progress bar, and integration with the native browser MediaSession API.
- **Embedded Content Viewer**: In-app modal viewer for videos and articles with native YouTube and Odysee embed conversions.
- **Search & Channel Filtering**: Instant live search and filtering by specific channels or content type (All, Posts, Podcasts).
- **Web Push Notifications**: Browser-based notifications alerting you to new posts from your subscribed channels.
- **Lightweight & Fast**: PHP-powered static site generator compiles feed data into `public/index.html` and `public/feed.json` for lightning-fast frontend delivery.
- **Mobile Responsive & PWA Ready**: Mobile-first layout with web app manifest and Service Worker support.

---

## Directory Structure

```text
rssTea/
├── feeds.php         # Core PHP script for fetching, parsing, and building feeds
├── feeds.txt         # List of RSS/Atom feed URLs (one per line)
├── base.html         # HTML template used to generate public/index.html
├── feed.json         # Compiled JSON output containing parsed feed items
├── others.txt        # Supplementary feed references/backup
└── public/           # Production web root directory
    ├── index.html    # Compiled main application page
    ├── feed.json     # Copy of compiled feed JSON for web app consumption
    ├── theme.css     # CSS stylesheet for YouTube-like styling
    ├── manifest.json # Web app manifest
    └── *.png / *.ico # Favicons and app branding assets
```

---

## Getting Started

### Prerequisites

- **PHP 7.4+** with `curl` and `simplexml` extensions enabled.
- A local or production web server (e.g., Apache, Nginx, or PHP's built-in web server).

### Installation & Usage

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/rssTea.git
   cd rssTea
   ```

2. **Configure your feeds:**
   Add feed URLs to `feeds.txt` (one URL per line). For example:
   ```text
   https://odysee.com/$/rss/@BrodieRobertson:5
   https://odysee.com/$/rss/@SomeOrdinaryGamers:6
   https://techlore.tv/feeds/videos.xml?videoChannelId=4
   ```

3. **Fetch feeds & generate web pages:**
   Run the build script to parse feeds and produce `public/index.html`:
   ```bash
   php feeds.php
   ```

4. **Serve the website:**
   Point your web server to the `public/` directory, or use PHP's built-in server for quick testing:
   ```bash
   php -S localhost:8000 -t public/
   ```
   Open `http://localhost:8000` in your browser.

---

## Automation / Cron Setup

To keep your feed updated automatically, schedule a cron job to run `feeds.php` at regular intervals:

```cron
0 * * * * cd /path/to/rssTea && php feeds.php >/dev/null 2>&1
```

---

## Credits

Created and maintained with inspiration from modern media web apps. Special credit to [Awesome Dev](https://github.com/avadhesh18)!

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
