# MCP development Warbun

Installed and verified 2026-10-01. Restart Codex after changing MCP configuration, then open this Warbun project to load its local server. Project-scoped configuration requires this project to be trusted in Codex.

| Server | Scope | Installed version | Verification |
| --- | --- | --- | --- |
| Playwright | Global Codex | Existing installation | Handshake and 25 browser tools |
| Context7 | Global Codex | @upstash/context7-mcp 4.1.1 at setup | Handshake, 2 tools, Laravel documentation library lookup |
| GitHub | Global Codex | Official native binary v1.12.2, Darwin arm64 | Release SHA256 verified, handshake, 43 tools, authenticated get_me |
| Laravel Boost | Warbun only | laravel/boost v2.10.1 | Handshake, 10 tools, application-info |

Global settings are in the local `~/.codex/config.toml`. Context7 uses `npx -y @upstash/context7-mcp@latest`; its anonymous documentation lookup passed without a newly supplied API key. Playwright configuration remains enabled. Packages launched with `@latest` may change version on later launches.

GitHub runs the official release from `~/.codex/bin/github-mcp-server` through `~/.codex/mcp/github-launch.py`. The launcher obtains the existing GitHub CLI credential at startup and passes it to the server process. No credential value is stored in this repository, launcher, or MCP config. Toolsets are context, repos, issues, pull_requests and users, matching development scope. On another computer, install the matching official release and authenticate GitHub CLI before registering the launcher.

Boost is a Composer development dependency. `.codex/config.toml` registers it for this repository; its launcher finds the Git repository root before executing `php artisan boost:mcp`, so it also works from a project subdirectory without committing a machine-specific absolute path. Existing AGENTS.md, skills and application guidelines are preserved; the MCP was registered directly rather than regenerating them with boost:install. On a new machine, run the project's normal Composer installation including development dependencies and open the trusted Warbun project in Codex.

Official configuration references: [Codex MCP](https://learn.chatgpt.com/docs/extend/mcp?surface=cli), [Context7](https://context7.com/docs/resources/developer), [GitHub server](https://github.com/github/github-mcp-server), [Laravel Boost](https://laravel.com/framework/docs/13.x/boost).

## Actual QA results

- `python3 scripts/qa-mcp-setup.py`: PASS. Actual JSON-RPC initialize, tools/list and read calls. Server output and credential/profile contents are not printed. Playwright verification checks tool discovery without launching a browser.
- Scoped configuration: all four servers load from Warbun; Boost is absent from the parent workspace. Codex app-server config/read also confirmed project configuration loading after registering the exact trusted project path in local user config.
- Boost launcher also tested from `resources/`, resolving the correct project root.
- `php artisan test`: 90 tests / 741 assertions PASS on isolated SQLite.
- `composer validate --no-check-publish`: PASS. Existing dependency versions unchanged; five development dependencies added: Boost, Laravel MCP, Laravel Roster, Composer Semver and Symfony YAML.
- `git diff --check`: PASS. No database migration, seeder or business write run against existing data. No UI source changed and no new browser transaction required for this setup.

The QA script reads configured servers from the local Codex installation. Re-running it needs Codex, Node/npm, PHP, Git and the existing GitHub CLI login. It verifies GitHub identity and documentation lookup, not every GitHub write operation or every Boost tool.
