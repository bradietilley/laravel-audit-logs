# Compatibility

## Supported versions

| PHP | Laravel | Status |
| --- | --- | --- |
| 8.3, 8.4 | 13.x | Supported |

## Versioning

This package follows [Semantic Versioning](https://semver.org). Breaking changes
are reserved for major releases.

## Notes for contributors

```bash
composer test          # Pest
composer analyse       # PHPStan (level max)
composer format        # Pint (autofix)
composer format:check  # Pint (check only)
```

All three must pass before a change is merged.
