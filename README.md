# Media Text (Omeka S module)

This Omeka S module allows to manage media textual representations
(transcriptions, translations, transliteration, ...).

They can be imported from files, visualized, modified, and used in several ways
(for display, search, ...).

## Requirements

- Omeka S ≥ 4.0.0
- PHP extension zlib

If automated text extraction is needed, the following programs are required:

- `pdftotext` (for .pdf files)
- `odt2txt` (for .odt files)
- `docx2txt` (for .docx files)
- `catdoc` (for .doc files)
- `tesseract` (for .png files)

## Quick start

1. [Install the module](https://omeka.org/s/docs/user-manual/modules/#adding-modules-to-omeka-s)
2. In the admin interface, when editing a media there is a new tab "Textual representations". Click the
   tab, then choose an option in the sidebar to create new textual representations.

## Features

- Supported formats: plain text, ALTO
- Resource page block rendering textual representation as plain text
- Automated text extraction for .pdf, .odt., docx, .doc and .png files
- Integration with the [Solr](https://github.com/biblibre/omeka-s-module-Solr)
  module (makes the text content searchable)
- Text documents are compressed using zlib to save disk space

## Contributors / Sponsors

This module was sponsored by:

- [École nationale supérieure des sciences de l'information et des bibliothèques (ENSSIB)](https://www.enssib.fr/)

## License

This module is distributed under the GNU General Public License, version 3
(GPLv3).  The full text of this license is given in the `LICENSE` file.
