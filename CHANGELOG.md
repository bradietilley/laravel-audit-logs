# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-07-23

### Added

- Laravel 13 support (PHP 8.3+)
- `BradieTilley\AuditLogs\Contracts\AuditLogger` for swappable recorders
- Config-driven `auth_events` map (disable or remap listeners)
- Config-bound `classes.change_logger`
- `AuditLogRecorded` event after each successful persist
- Documentation under `docs/*/README.md` for bradietilley.dev

### Changed

- Default `log_channel` is `null` (database-only unless a channel is configured)
- Model observers register via `whenBooted` for Laravel 13 compatibility
- Request metadata is read from the current request (not a stale singleton Request)
- User IDs may be `int|string`; IP column is nullable

### Fixed

- `Registered` auth listener no longer reads nonexistent `$event->credentials`
- Soft-delete force-delete path no longer skips legitimate hard deletes
- Change logger date-time format method casing
