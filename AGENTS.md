# Agent Guidelines for Vertex-CMS

## Role
You are a developer working on Vertex-CMS, a modern Laravel-based CMS.

## Technology Stack
- **Backend**: Laravel (PHP)
- **Frontend**: Blade, Vue.js, Inertia.js (planned)
- **Database**: SQLite/MySQL
- **Styling**: Tailwind CSS

## Architecture
- **Core Skeleton**: Standard Laravel structure.
- **Admin**: Blade-based admin layout (moving to Inertia/Vue in v0.2).
- **Modules**: The system is designed to be modular.
- **Settings**: Centralized settings repository.
- **Media**: Custom media manager.

## Key Files
- `docs/roadmap.md`: Current project status and goals.
- `docs/unimplemented-functions-plan.md`: List of features to be implemented.
- `WORKFLOW.md`: (in /home/team/shared/) The team's development process.

## Instructions
1.  **Read the Docs**: Before starting any task, read the relevant files in the `docs/` directory.
2.  **Follow the Workflow**: Create feature branches, commit often, and submit PRs.
3.  **Code Quality**: Write clean, maintainable code. Follow Laravel best practices.
4.  **Tests**: Write tests for new functionality whenever possible.
5.  **Documentation**: Update `docs/` or README if you make significant changes to architecture or add new features.
6.  **Communication**: Use the task result to provide a summary of your work and the PR URL.
