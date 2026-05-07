# Team Vertex-CMS Log

## [2026-05-07] MVP Phase 1 Improvements

### Completed
- **Data-driven `robots.txt`**: Implemented `seo.robots_txt` setting in the database and updated `RobotsController` to use it. Added feature tests. (Engineer)
- **Admin UI Polish**: 
    - Added Heroicons to sidebar navigation.
    - Improved mobile responsiveness with a slide-over menu.
    - Added "View Site" link to the header.
    - Implemented dynamic breadcrumbs with Russian translations. (UI Engineer)
- **Block Template Library**: Created 26+ Blade templates for all major block types (heading, text, image, gallery, interactive components, etc.) in `resources/views/builder/blocks/`. Integrated Alpine.js for interactivity. (Engineer)

### In Progress
- **Multi-language (i18n) Infrastructure**: Setting up Laravel localization and multi-language content support for pages. (Engineer)
- **UI Modernization & Dark Mode**: Implementing a sleek dark theme and modernizing overall UI components. (UI Engineer)
- **Page Builder UX Enhancement**: Adding Undo/Redo, search functionality, and improved canvas visualization. (UI Engineer)
- **Comprehensive MVP Audit**: QA-engineer is performing an end-to-end audit of the installer, permissions, and media manager. (QA Engineer)

### Upcoming
- Full end-to-end verification of the "Install -> Create -> Publish" flow.
- Integration of the modernized UI with the new block templates.
