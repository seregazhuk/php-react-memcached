# Change Log
All notable changes to this project will be documented in this file.

## 0.2.1 - 2018-09-14
### Updated:
 - Move to final classes and type-hints

## 0.2.0 - 2018-06-26
### Updated:
  - required php version changed to 7.2

## 0.1.6 - 2018-06-26
### Fixed:
 - updated composer dependencies

## 0.1.5 - 2018-01-03
### Fixed:
 - protocol parser throws `WrongCommandException` when parsing response for unknown command

## 0.1.4 - 2017-11-19
### Fixed:
 - remove trailing slash in commands with no arguments (like `version`)
 - cache key parsing from read response

## 0.1.3 - 2017-11-17
### Fixed:
 - method type hints for IDE auto-completion

## 0.1.2 - 2017-11-07
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
 
## 0.1.1 - 2017-10-19
### Fixed:
 - autoload in composer.json

## 0.1.0 - 2017-10-18
- First tagged release

## 0.0.0 - 2017-10-08
- First initial commit 
