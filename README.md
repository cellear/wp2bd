# WP4BD - WordPress Theme Compatibility Layer for Backdrop CMS

**Bring classic WordPress themes to Backdrop CMS without modification.**

## What is this?

WP4BD (WordPress for Backdrop) is a backward compatibility layer that allows classic WordPress themes (pre-Block Editor era) to run on Backdrop CMS 1.30. It intercepts WordPress function calls and template suggestions, mapping them to Backdrop's architecture.

## Why?

- **Effortless migration** from WordPress to Backdrop
- **Preserve theme investments** without rewriting code
- **Reduce transition costs** dramatically
- **Extend the life** of existing theme assets

## Proof of Concept Target

The initial PoC successfully runs the **WordPress Twenty Seventeen** theme (WordPress 4.9 era) on Backdrop CMS 1.30 without any theme modifications.

## Project Components

1. **WP-TCL Module** - The compatibility layer that implements WordPress function stubs and the Loop logic
2. **Bridge Theme** - A Backdrop theme that wraps and loads WordPress theme files
3. **Twenty Seventeen** - The target WordPress theme for initial testing

## Scope

**Included:**
- Classic Editor era themes (WordPress 4.9 and earlier)
- Core template tags and Loop functionality
- Template redirection mechanism
- Asset loading and path management

**Excluded:**
- Block Editor (Gutenberg) themes
- Complex WordPress plugin APIs
- Full WordPress module compatibility

## Status

🚧 **In Development** - This is an active proof of concept project.

## Documentation

For detailed implementation plans, phases, and technical specifications, see:
- [Project Plan: WordPress Theme Compatibility Layer.md](Project%20Plan_%20WordPress%20Theme%20Compatibility%20Layer.md)

## Version Context

- **Target WordPress Version:** 4.9 (last major pre-Gutenberg release)
- **Target Backdrop Version:** 1.30
- **Theme Target:** Twenty Seventeen

## License

[To be determined]
