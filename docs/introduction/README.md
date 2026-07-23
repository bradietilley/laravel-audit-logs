# Introduction

Laravel Audit Logs records notable events in your application — model creates,
updates, deletes, and authentication activity — into a queryable database table,
with an optional stream write to a Laravel log channel.

## What it is for

- **Regulatory auditing** — pair the optional log stream with an immutable sink
  (CloudWatch, etc.) for durable evidence of who did what.
- **Resource history** — each log morphs to the affected model so you can show
  a timeline of changes on a resource.
- **User activity** — each log morphs to the acting user so you can trace a
  user's steps through the app.
- **Reporting hooks** — listen for [`AuditLogRecorded`](../customization/README.md#auditlogrecorded)
  to feed analytics without subclassing the logger.

## Design goals

- Small surface: a trait/interface on models, a recorder for ad-hoc logs, and
  config for the rest.
- Sensible defaults for change formatting (ignore noise, redact secrets, truncate
  long strings) with escape hatches when you need more.
- Dual write: database for querying; optional PSR log channel for streaming.

## What it is not

- Not a full SIEM or compliance suite.
- Not a UI — you query Eloquent relations or the `AuditLog` model yourself.
- Not a before/after diff engine — update logs describe the new values in
  human-readable form (see [Change Logging](../change-logging/README.md)).

## Next steps

- [Installation](../installation/README.md)
- [Activity Logs](../activity-logs/README.md)
