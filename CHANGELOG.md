# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- Fix catdoc extractor: it now handles .doc files (application/msword)
  instead of .odt files

## [0.2.1] - 2026-10-15

- Fix integration with Solr module

## [0.2.0] - 2026-07-28

- Move textual representations management interface to the media modification
  page
- Add textual representations extractors (pdftotext, catdoc, docx2txt, odt2txt,
  tesseract)
- Add public/private visibility setting for textual representations
- Allow batch deletion of textual representations for items and media
- Allow batch extraction of textual representations for items and media
- Add background job to recompress all textual representations


## [0.1.0] - 2026-07-17

Initial release

[0.2.1]: https://github.com/biblibre/omeka-s-module-MediaText/releases/tag/v0.2.1
[0.2.0]: https://github.com/biblibre/omeka-s-module-MediaText/releases/tag/v0.2.0
[0.1.0]: https://github.com/biblibre/omeka-s-module-MediaText/releases/tag/v0.1.0
