# Match the local navbar wordmark

> Only [selected previews](../PREVIEWS.md) are retained in Git. Export filenames below describe the original review run; bulk PNG/PDF evidence can be regenerated from the final native document.

31 October 2026. Same native Figcraft document.

Inspected the running navbar at http://127.0.0.1:8000/ using Playwright, computed CSS and Chromium CSS.getPlatformFontsForNode. Actual word: lowercase `warbun`. CSS: system-ui/sans-serif, weight 800, letter spacing -0.065em, line height 1, desktop 32 px. Project CSS uses 29 px for the smaller navbar.

Chromium on this Mac resolves the six logo glyphs to `.SF NS`, with optical size 32 and weight 800. Loaded the already-installed `/System/Library/Fonts/SFNS.ttf` into the Figcraft session, where its family is `System Font`. Native logo text uses that family with variable axes wght=800, opsz equal to size, wdth=100 and GRAD=400. Explicitly named browser font-family candidates did not reproduce system-ui metrics, so they were not substituted merely by name.

Updated 144 actual wordmark text nodes to lowercase and -6.5% tracking. Navbar references use 29 px mobile and 32 px desktop; illustration wordmarks retain their existing proportional sizes and are centered in their sign area. Existing brand colors and non-logo typography remain unchanged.

Visually reviewed mobile light/dark and desktop light renders, inspected the resulting font variation properties, and refreshed the four main theme PDFs and landing PNGs. Native file saved. The system font is referenced, not copied into the repository; native editing on other machines depends on compatible font availability. No application source or database changes.
