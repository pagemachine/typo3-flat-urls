# Flat URLs ![CI](https://github.com/pagemachine/typo3-flat-urls/workflows/CI/badge.svg)

Flat URLs (like Stack Overflow) for TYPO3

## Installation

This extension is installable from various sources:

1. Via [Composer](https://packagist.org/packages/pagemachine/typo3-flat-urls):

        composer require pagemachine/typo3-flat-urls

2. From the [TYPO3 Extension Repository](https://extensions.typo3.org/extension/flat_urls/)
3. From [Github](https://github.com/pagemachine/typo3-flat-urls/releases)

## Purpose

The purpose of this extension is to enforce so called "flat URLs" throughout the TYPO3 frontend. Thus instead of `my/deeply/nested/page/` you will always get URLs like `10/page/` (thus page UID and title), no matter the actual page hierarchy managed in the backend.

Page translations will use the same UID as their original page but with translated titles. Thus you need to make sure that the language parameter (`_language`) is part of the URL to avoid duplicate URLs with multiple translations.

For flat URLs this extension manages the slugs of pages, thus these are not editable anymore. Whenever a page is edited the slugs are updated automatically so they stay in sync with their related fields (title by default). If a page slug changes, a redirect is added automatically. Conflicting redirects when changing slugs back and forth are removed to ensure pages are always accessible.

Similar to Stack Overflow pages requested only by page UID will be redirected to their full URL. This means that e.g. https://example.org/10/ redirects to https://example.org/10/page/. This also works for translations.

## Command

If you have existing pages when adding this extension or if you want to ensure a clean state you can run the `slugs:update` CLI command. It will update the slugs of all pages and page translations.

## Testing

All tests can be executed with the shipped Docker Compose definition:

    docker-compose run --rm app composer build
