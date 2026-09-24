# Agint Pricing Cards

A lightweight, dependency‑free WordPress plugin for building beautiful, responsive **pricing cards** — with a modern admin, a per‑card icon toggle, a description line above the feature list, a "Popular" badge, and an optional **carousel** layout.

Render anywhere with a shortcode:

```
[agint_pricing_cards]
```

## Features

- **Visual admin manager** — a master/detail screen: pick a plan on the left, edit it on the right. Add, duplicate, delete and reorder plans; no code required.
- **Per‑plan control** — title, currency, price, period, description, features (one per line), button text/link.
- **Icon per card** — pick from the Media Library, with a show/hide toggle.
- **"Popular" plan** — highlight one card and show a badge (custom badge text).
- **Grid or Carousel** — the carousel is pure vanilla JS (no jQuery/Swiper) with responsive slides‑per‑view, arrows, dots, loop, autoplay and touch/drag swipe.
- **Equal‑height cards** — header, price, divider and button align on one level across all cards.
- **Accent colour** presets to match your brand.
- **Secure & standards‑based** — class‑based OOP, nonces + capability checks, sanitised input, escaped output, i18n‑ready, assets loaded only where needed.

## Installation

1. Download the latest `agint-pricing-cards.zip` (or clone this repo and zip the `agint-pricing-cards` folder).
2. In WordPress: **Plugins → Add New → Upload Plugin**, choose the zip, **Install Now**, then **Activate**.
3. Open the **Pricing Cards** menu to manage plans and design.
4. Place `[agint_pricing_cards]` on any page or post.

## Shortcode

```
[agint_pricing_cards]                    <!-- uses your saved settings -->
[agint_pricing_cards columns="2"]        <!-- cards per row / per view (1–4) -->
[agint_pricing_cards layout="carousel"]  <!-- force the carousel for one placement -->
```

`[apc_pricing_cards]` is an equivalent alias.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Author

**Mohammed Freihat**

## License

GPL‑2.0‑or‑later
