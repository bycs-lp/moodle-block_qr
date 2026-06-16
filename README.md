# block_qr - QR Code block for Moodle

The `block_qr` plugin adds a configurable block that generates QR codes directly in Moodle contexts (course pages, activities, dashboard, and more).

It is designed for quick sharing of links and structured content to mobile devices without copying URLs manually.

## Features

- Generate QR codes directly from a block instance.
- Multiple QR content modes (links, internal Moodle targets, event, geolocation, WiFi).
- Configurable QR size per block instance.
- Optional integration with a URL shortener service.
- Multiple block instances are supported.

## Available QR target modes

When editing a block instance, you can choose one of the following modes via **Content** (`config_options`):

1. `currenturl` - **Link to the current page**  
   Encodes the exact URL of the page where the block is shown.

2. `courseurl` - **Link to this course**  
   Encodes the course URL and optionally allows a description text.

3. `internalcontent` - **Link to a section or activity**  
   Lets you select either:
   - a course section (`section=<sectionid>`), or
   - a course module/activity (`cmid=<cmid>`).

4. `owncontent` - **Text/URL**  
   Encodes custom text or a custom URL (for links, use `https://...`).

5. `event` - **Event**  
   Encodes iCalendar-style event data including title, location, start, end, and all-day option.

6. `geolocation` - **Geolocation (lat/lon)**  
   Encodes coordinates and can optionally include a map link target.

7. `wifi` - **WiFi**  
   Encodes WiFi setup information (SSID, hidden SSID flag, authentication type, optional password).

## Configuration

### Block instance settings

Each block instance can configure:

- Content mode (see list above)
- Mode-specific fields (e.g., section/activity target, event data, geolocation, WiFi settings)
- QR size (`small`, `medium`, `large`)

### Global plugin setting

The plugin supports a configurable short-link service URL (`configshortlink`).
If configured, a short-link action can be shown in block editing mode.

## Installation

### Installing via uploaded ZIP file

1. Log in to your Moodle site as an admin and go to _Site administration > Plugins > Install plugins_.
2. Upload the ZIP file containing this plugin.
3. Follow the installation steps and confirm plugin validation.

### Installing manually

Copy this directory to:

    {your/moodle/dirroot}/blocks/qr

Then complete installation via _Site administration > Notifications_ or run:

    php admin/cli/upgrade.php

## Requirements

- Moodle version: see `version.php` (`$plugin->requires`)
- Supported Moodle branches: see `version.php` (`$plugin->supported`)

## License

2023 ISB Bayern  
Author: Florian Dagner <florian.dagner@outlook.de>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
