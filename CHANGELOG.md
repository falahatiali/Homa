# Changelog

All notable changes to Homa will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Groq Provider Support** - Ultra-fast AI inference with OpenAI-compatible API
- **Multiple Groq Models** - Support for GPT-OSS, Llama, Mixtral, and Gemma models
- **Comprehensive Environment Template** - Complete .env.example with all providers
- **Enhanced Documentation** - Updated README with Groq examples and setup instructions
- **Usage Examples** - Added examples/groq-usage.php with comprehensive demos
- **Performance Comparison** - Tools to compare speed between different providers

### Changed
- **Provider Count** - Now supports 4 AI providers (OpenAI, Anthropic, Grok, Groq)
- **Configuration** - Enhanced config/homa.php with Groq settings
- **Documentation** - Improved setup instructions and provider comparison

### Fixed
- **GrokProvider** - Cleaned up implementation and removed debugging artifacts
- **Environment Setup** - Added comprehensive .env.example template

### Security
- Secure API key storage via environment variables
- Input sanitization for API calls

## [1.0.3] - Previous Release

