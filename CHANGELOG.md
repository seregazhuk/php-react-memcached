# Change Log
All notable changes to this project will be documented in this file.

## v0.1.5 - 2018-01-03
### Fixed:
 - protocol parser throws `WrongCommandException` when parsing response for unknown command

## v0.1.4 - 2017-11-19
### Fixed:
 - remove trailing slash in commands with no arguments (like `version`)
 - cache key parsing from read response

## v0.1.3 - 2017-11-17
### Fixed:
 - method type hints for IDE auto-completion

## v0.1.2 - 2017-11-07
### Added:
 - functional tests
 - client can emit events (`close`, `error`)

### Fixed:
 - connection auto-recovering
 - `createClient()` uses default Memcached address
 - don't allow new requests when *is ending* or *is closed*
 - write response resolving
 - wrong commands are rejected immediately
 - handle failed commands 
 - handle broken connection
 - retrieve value after decrement
 
## v0.1.1 - 2017-10-19
### Fixed:
 - autoload in composer.json

## v0.1.0 - 2017-10-18
- First tagged release

## v0.0.0 - 2017-10-08
- First initial commit 
