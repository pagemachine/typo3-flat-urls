# Flat URLs [![Build Status](https://travis-ci.org/pagemachine/typo3-flat-urls.svg)](https://travis-ci.org/pagemachine/typo3-flat-urls)

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

The flat URLs are a fully managed path segment for every page.

Similar to Stack Overflow pages requested only by page UID will be redirected to their full URL. This means that e.g. https://example.org/10/ redirects to https://example.org/10/page/. This also works for translations.

## Command

If you have existing pages when adding this extension or if you want to ensure a clean state you can run the `slugs:update` CLI command. It will update the slugs of all pages and page translations.
