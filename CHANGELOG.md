# Changelog

## v5

- `require_z_version` config option was dropped, now core module only specify `minimum_z_version`
- admin now must specify it's own includes (names starting with `admin.`)
- 404 now doesn't redirect, shows error in debug mode, shows notfound page otherwise
- view templates now can see data directly (no need to call getData() anymore)
- partial templates cannot use getData('partial.<mydata>') anymore, must access data directly
- partial templates can now be used as includes (`includePartial('partial-name');`)
- cookies module improved (can show warnings about 3rd party cookies and when cookies are disabled in browser)
- added new include placement - `top` (and corresponding `admin.top` too)
