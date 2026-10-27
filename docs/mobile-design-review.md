# Mobile dashboard design review

Reviewed 27 October 2026. Proposal only; no application behavior changed.

Implementation follow-up: the user selected Daylight Station with Night
Observatory informing Astro and dark mode. This review preserves the original
alternatives; the adopted conventions and verification steps are documented in
`AGENTS.md` and `docs/development.md`.

## Scope and method

Visual screening of 30 public design shots from Dribbble's mobile-weather search, including weather dashboards and two adjacent monitoring dashboards. Reviewed the visible gallery artwork, not working applications or complete interaction flows. Separate shots from the same designer/product count as separate designs; they are not 30 independent products. Excluded login-only screens, standalone widget sets, and travel/world-clock layouts. Rankings below are judgments about suitability for this station, not measured usability outcomes. Small labels and hidden navigation could not always be established from thumbnails.

Compared visible hierarchy, navigation discoverability, separation of global destinations from local controls, mobile data density, and relevance to weather/astronomy. Each reference below records a visible feature and a proposed lesson; lessons are recommendations, not claims about hidden functionality.

## The 30 designs

| # | Reference | Visible feature | Lesson for this application |
|---|---|---|---|
| 1 | [WanderWeather / UI UX](https://dribbble.com/shots/25319214-WanderWeather-UI-UX-Design-for-Mobile-Weather-App) | Landscape hero, compact metric cluster, segmented content | Keep destination navigation distinct from content segments; photography should not displace readings. |
| 2 | [Mobile / Weather app — Purrweb Graphics](https://dribbble.com/shots/20675054-Mobile-Weather-app) | Yellow temperature hero, menu icon, weekly strip | Excellent reading hierarchy; a hidden menu alone would make Astro harder to discover. |
| 3 | [Weather Monitoring — Cansaas](https://dribbble.com/shots/27640086-Weather-Monitoring-App) | Metric grid, dedicated forecast/detail screens, bottom icons | Closest structural fit: summary first, drill into a metric, preserve global navigation. |
| 4 | [Weather UI — Expert App Devs](https://dribbble.com/shots/26188012-Weather-App-UI-Design) | Current summary, hour strip, seven-day list | Distinguish selection of a time from navigation to another feature. |
| 5 | [Weather — Imran Hossen](https://dribbble.com/shots/14310353-Mobile-application-Weather-App) | Bright condition card and forecast lists | Group information into a few readable cards; reduce illustration size for station data. |
| 6 | [Weather Forecast — Outcrowd](https://dribbble.com/shots/6932038-Mobile-application-Weather-Forecast) | Today/tomorrow/week labels and separate weekly view | Useful local time scope; do not turn time scopes into global destinations. |
| 7 | [Ambient Weather — RonDesignLab](https://dribbble.com/shots/25157738-Ambient-Weather-Mobile-App-Dashboard) | Dark station dashboard, day selector, compact readings | Strong monitoring precedent; retain units and avoid decorative gauges without purpose. |
| 8 | [Weather Concept — Voyager](https://dribbble.com/shots/23991244-Weather-Mobile-App-Concept) | Monochrome hero, bright condition tag, lower chart | Strong visual personality, but decorative objects compete with useful information. |
| 9 | [WanderWeather / Mobile Design](https://dribbble.com/shots/25550154-WanderWeather-Weather-Mobile-App-Design) | Scene-led summary with compact mode switch | A night preview can be atmospheric while keeping the mode switch explicit. |
| 10 | [Air Quality & Weather — Tran Mau Tri Tam](https://dribbble.com/shots/10165678-Air-Quality-Weather-Mobile-App-Concept) | Summary/chart screen and separate map screen | Separate summary from spatial exploration; do not add a map destination without a real data source. |
| 11 | [ForecastFit — Lay](https://dribbble.com/shots/25448239-ForecastFit-Weather-Mobile-App-Concept) | Editorial summary, large temperature, forecast action | Translate conditions into a useful sentence, with detailed readings still accessible. |
| 12 | [WEather — BL/S](https://dribbble.com/shots/24188566-WEather-Mobile-App) | Warm single-screen temperature and compact lower controls | Useful warmth for a field-journal direction; labels matter more than small icon treatments. |
| 13 | [Weather Concept — Nina Skrbic / metrics](https://dribbble.com/shots/25023491-Weather-Mobile-App-Concept) | Dense monochrome metric tiles alongside a map | A compact instrument view can work; avoid making every measurement equally prominent. |
| 14 | [Weather Mobile — Fireart](https://dribbble.com/shots/25194038-Weather-Mobile-App) | Restrained summary, dedicated forecast list, bottom navigation | Strong precedent for shallow navigation and quieter cards. |
| 15 | [Brutalist Data Command Center — sleek.design](https://dribbble.com/shots/27263099-Weather-Mobile-App-UI-Brutalist-Data-Command-Center) | Distinct live, data and radar screens; dense dark typography | Separate real-time status from analysis; avoid tiny technical text. |
| 16 | [Weather UI Concept — Ibrahim Khalil](https://dribbble.com/shots/26692417-Weather-Mobile-App-UI-Concept) | Pale cards, hour strip and metric grid | Clear grouping; preserve contrast and label global destinations. |
| 17 | [Weather Concept — Nina Skrbic / temperature](https://dribbble.com/shots/24984957-Weather-Mobile-App-Concept) | Large temperature, warm gradient and lower hour row | One dominant number is effective; other station readings need room below it. |
| 18 | [Weather iOS — Purrweb](https://dribbble.com/shots/26421389-Weather-Mobile-iOS-App-Design) | Yellow temperature composition and weekly strip | Reinforces #2; same-family artwork is not independent usability evidence. |
| 19 | [Wave Weather — Andrii Bondar](https://dribbble.com/shots/2919084-UI-animation-Wave-Weather-mobile-and-watch-app) | Minimal temperature over a wave-like background | Expressive simplicity suits a glance view, but cannot carry the whole station workflow. |
| 20 | [Map & Precipitation — Orix](https://dribbble.com/shots/27447337-Weather-App-UI-Mobile-Map-Precipitation-Dashboard) | Map layer segments and lower detail sheet | Useful local controls; layers should not replace global destinations. |
| 21 | [Weather / Forecast — ByteTown](https://dribbble.com/shots/26894376-Weather-Weather-App-Weather-Forecast-Mobile-App-UIUX) | Map above dense multi-metric forecast matrix | Relevant to the Astro hourly matrix; carry explicit row labels and units. |
| 22 | [WanderWeather / Forecast](https://dribbble.com/shots/27233467-Weather-Forecast-Mobile-App-UI-WanderWeather) | Globe/map canvas with bottom controls and layer menu | Distinguish location, layer, and app navigation; simplify heavily for this station. |
| 23 | [Weatherly — Faruk Hossain](https://dribbble.com/shots/27624131-Weatherly-AI-Weather-Forecast-Mobile-App-UI-UX) | Blue condition cards, detail grid and hour strip | Familiar summary-to-detail hierarchy; avoid spending the first screen on decoration. |
| 24 | [Weather Forecast — Faruk Hossain](https://dribbble.com/shots/26555651-Weather-Forecast-Mobile-App) | Forecast summary, map view and bottom dock | Strong separation of content views; use a full-width safe-area bar for production. |
| 25 | [Modern Weather Forecast — Abdullah](https://dribbble.com/shots/27705014-Weather-App-UI-Modern-Weather-Forecast-Mobile-Design) | Condition hero followed by metric and hourly blocks | Familiar scan order; show archive observation time near the hero. |
| 26 | [Weather / ski — Purrweb](https://dribbble.com/shots/23299672-Weather-App) | Illustration-led temperature and recent snowfall chart | Domain-specific measurements improve relevance; use station and night-planning measures. |
| 27 | [Weather & Energy Monitoring — Gridfox](https://dribbble.com/shots/27709901-Weather-Energy-Monitoring-Mobile-App-UI) | Dark instrument display, metric tiles and energy chart | Adjacent monitoring reference; useful grouping, but avoid luminous decorative chrome. |
| 28 | [Weather App Design — QClay](https://dribbble.com/shots/24469664-Weather-App-Design) | Purple condition hero over a pale hourly section | Strong atmospheric direction; keep chart surfaces and text contrast stable. |
| 29 | [Smart home control — Pocolo](https://dribbble.com/shots/24589335-Mobile-application-for-smart-home-control) | Weather and energy summary cards above a bottom dock | Adjacent dashboard reference: make cards entry points while retaining app navigation. |
| 30 | [Weather App Design — Awsmd](https://dribbble.com/shots/22863114-Weather-App-Design) | Different condition palettes with recurring metric layout | Change atmosphere without moving navigation or changing metric meaning. |

## Strongest navigation patterns, ranked for this application

1. **Persistent labelled bottom navigation.** Retain Dashboard, Trends, Astro and Menu. Put a visible selected state on the current section, with an icon plus text. Best structural references: #3 and #14; #29 contributes the dashboard/dock relationship. Four destinations fit this application without promoting every historical page to a tab.
2. **Local segmented controls below the page heading.** Keep day/week/month in Trends and night selection in Astro. These change the current view, not the global destination. References #6, #7 and #20 show useful local scope/layer distinctions.
3. **Metric cards that lead into detail.** Tap Temperature to open the existing trend route with outTemp selected. Make the affordance and accessible name explicit; retain the selected time range and restore it on Back. References #3, #13 and #29 support the summary/detail structure; behavior is proposed here.
4. **A grouped secondary menu.** Use Explore data, Annual reports, Tools & settings, and Connected systems. Annual reports must link straight to the shared workspace. Keep the menu shallow and return focus to its trigger on close. The screenshot review gives weaker evidence for hidden menu behavior, so this recommendation mainly follows the application's existing information architecture.

Avoid icon-only primary navigation, gesture-only changes, a floating dock that wastes screen space, more primary tabs without usage evidence, and separate global tabs for individual weather measurements.

### Proposed route structure

- Dashboard → current conditions; metric cards → Trends with metric preselected.
- Trends → local metric and range controls; preserve URL state.
- Astro → complete sunset-to-sunrise nights; local night selector and labelled hourly matrix.
- Menu → Explore data; Annual reports (direct /reporttempyeartotals.php); Tools & settings; Connected systems.

Keep the bar flush with the viewport bottom, reserve its height plus env(safe-area-inset-bottom), and size menu overlays with 100dvh. Use roughly 44px touch targets, visible keyboard focus, aria-current for routes, and labels that do not depend on color. Keep real links for route changes and buttons for local state.

## Three visual directions

### Daylight Station — recommended default

Cool mist background (#F2F6FA), white cards, navy typography (#10233F), blue actions (#2369A8). Preserve Roboto headings, Inter body and Source Sans Pro highlights. Use compact 12px-radius cards, light shadows and generous spacing between sections. Lead with temperature and observation time; show wind, rain and humidity next, followed by one trend preview and a Tonight link. Four labelled bottom destinations remain visible.

References: #3, #14, #16. Best for daily use, readable measurements and the smallest visual departure from the existing system. Tradeoff: less distinctive than the other directions. Dark counterpart uses navy surfaces with the same hierarchy.

### Field Journal — warm and editorial

Warm paper background (#F5F1E8), white cards, ink text (#24342E), forest accent (#315C49), restrained ochre (#A56C26). Keep the existing font families. Lead with a concise daily summary, then a compact observation list with linked metric rows, a daily high/low section and a clear Annual reports entry. Use quieter rounded corners and thin dividers within cards. The same global bar stays in place.

References: #11, #12, #17, with restraint from #14. Best for history, seasonal comparisons and a personal weather-station identity. Tradeoff: lower immediate metric density; the summary must never invent a forecast or imply a trend without data.

### Night Observatory — instruments and observing

Midnight surfaces (#0B1220), blue-grey cards (#152238), pale text (#E4EDF7), restrained lavender actions (#B7ACF4). Preserve existing typography and use tabular figures for aligned readings. On Dashboard, put current observations first, then a Tonight module; on Astro, lead with a night selector, an observing assessment and a shared sunset-to-sunrise timeline. Present sky quality, darkness and moon visibility in aligned rows. Keep Astro directly accessible in the same bottom bar.

References: #7, #15, #21, #27, #28. Best for frequent astronomy use and an instrument-like identity. Tradeoff: dark mode alone does not preserve dark adaptation; chart labels still need sufficient contrast. Do not show a best imaging window unless green sky/seeing, full astronomical darkness and the moon below the horizon overlap.

## Recommendation and validation

Use Daylight Station as the default visual language, with Night Observatory informing Astro and the dark theme. Field Journal is the alternative if station history is the main differentiator. These remain proposals, not new repository conventions.

Before implementation, test three tasks on a 390px phone: open tonight's Astro plan; open a temperature trend and change its range; reach annual rainfall from Annual reports. Compare completion, wrong turns and label comprehension. Check 320px layout, keyboard navigation, Safari toolbar changes, safe-area behavior and restored Back state. Keep observed/archive status distinct from MQTT-connected live status, Europe/London times, wind in km/h, one-pixel trend lines, and visible high/low callouts.

The accompanying conversation mockups use illustrative values, not fetched station data. They compare composition and navigation; they are not production screens.
