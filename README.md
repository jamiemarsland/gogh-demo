# gogh — take it for a spin

gogh is a freeform canvas for WordPress. Drag anything anywhere on the live page — the words, the pictures, the button, even the sky — and it publishes clean, responsive core blocks. Deactivation-safe, theme-native, no lock-in.

Every link below builds a fresh, throwaway WordPress site in your browser with gogh already installed and you already logged in. Nothing to install. Give it a minute to boot, and use a desktop browser: Chrome, Edge or Firefox.

## Try it

- **Elliot Grey — a photographer's site.** Editorial, black and white, the default demo.
  https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/jamiemarsland/gogh-demo/main/blueprint.json&storage=temp
- **The Yellow House — a little gallery in Arles**, classic header, no shop.
  https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/jamiemarsland/gogh-demo/main/blueprint-classic.json&storage=temp
- **The Yellow House with a shop** — WooCommerce, four prints, gogh's shop looks.
  https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/jamiemarsland/gogh-demo/main/blueprint-experiments.json&storage=temp
- **Halcyon — a film studio.** Dark, cinematic, parallax hero and veiled portfolio cards.
  https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/jamiemarsland/gogh-demo/main/blueprint-film-studio.json&storage=temp
- **Fenwick & Lowe — a one-page site.** An architecture practice on a single page: the menu is anchors, not pages, so every link glides to its section.
  https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/jamiemarsland/gogh-demo/main/blueprint-onepage.json&storage=temp
- **Hollowell — a ceramics studio's shop.** WooCommerce with twelve pots in four glazes: gogh's canvas around Woo's rails.
  https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/jamiemarsland/gogh-demo/main/blueprint-ceramics.json&storage=temp

Things to try once you're in: drag the headline somewhere else, roll the die on a section (the ⚄ in the section pill) to see three more takes on the same words, open the header from the page, and publish. The site lives only in that tab — closing or refreshing it starts over. Swap `storage=temp` for `storage=browser` at the end of a link if you'd rather it survived a refresh.

## What's in this repo

This repo is gogh's public face, updated automatically on every release:

- `gogh-playground.zip` — the current plugin build, **v0.99.498**
- `blueprint*.json` — the Playground blueprints above (plus a paste-HTML experiment)
- `demo-boot.php` and friends — the boot scripts the blueprints fetch
- `gogh-kb.md` — the knowledge base gogh's helper answers from

Install the plugin on your own site by uploading `gogh-playground.zip` through Plugins → Add New → Upload.

## Licence

GPLv2 or later. Made by [Jamie Marsland](https://github.com/jamiemarsland).
