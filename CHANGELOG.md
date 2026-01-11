# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-01-10

### Added

- Initial release of KYA SMS PHP SDK
- **SMS API**
  - Send simple SMS to single or multiple recipients
  - Send flash SMS
  - Send SMS with templates
  - Send bulk SMS to contact groups
  - Support for custom reference tracking
  - Wallet selection support
- **OTP API**
  - Initiate OTP verification
  - Support for custom OTP codes
  - Configurable expiration time
  - Multi-language support (fr, en, es, de)
- **Campaign API**
  - Create automatic (immediate) campaigns
  - Create scheduled campaigns with timezone support
  - Create periodic campaigns (weekly, monthly, yearly)
  - Campaign status tracking
  - Progress monitoring
  - Template support for campaigns
- **HTTP Client**
  - Automatic retry with exponential backoff
  - Request/response logging for debugging
  - Configurable timeouts
- **Error Handling**
  - Specific exception types for different errors
  - Detailed validation error messages
  - Rate limiting detection
- **Documentation**
  - Comprehensive README with examples
  - PHPDoc comments on all public methods
  - Example files for each API

### Security

- API key protection in logs (redacted)
- Secure HTTP headers
- Input validation

---

## Types of Changes

- `Added` for new features
- `Changed` for changes in existing functionality
- `Deprecated` for soon-to-be removed features
- `Removed` for now removed features
- `Fixed` for any bug fixes
- `Security` in case of vulnerabilities
