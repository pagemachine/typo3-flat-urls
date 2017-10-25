# Flat URLs [![Run Status](https://api.shippable.com/projects/58c13b12ba2953050018f4e3/badge?branch=master)](https://app.shippable.com/projects/58c13b12ba2953050018f4e3) [![Coverage Badge](https://api.shippable.com/projects/58c13b12ba2953050018f4e3/coverageBadge?branch=master)](https://app.shippable.com/projects/58c13b12ba2953050018f4e3)

Flat URLs (like Stack Overflow) for TYPO3

## Purpose

The purpose of this extension is to enforce so called "flat URLs" throughout the TYPO3 frontend. Thus instead of `my/deeply/nested/page/` you will always get URLs like `10/page/` (thus page UID and title), no matter the actual page hierarchy managed in the backend.

Page translations will use the same UID as their original page but with translated titles. Thus you need to make sure that the language parameter (`L`) is part of the URL to avoid duplicate URLs with multiple translations.

The flat URLs are achieved using RealURL and a fully managed path segment for every page. Whenever the title of a page is changed, its path segment is updated accordingly.

## Command

If you have existing pages when adding this extension or if you want to ensure a clean state you can run the `flaturls:update` CLI command. It will process all pages and page translations, generate the path segments and enforces them.
