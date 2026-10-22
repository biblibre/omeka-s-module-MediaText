Introduction
============

`Media Text <https://github.com/biblibre/omeka-s-module-MediaText>`__ allows to
manage media textual representations (transcriptions, translations,
transliteration, ...).

They can be imported from files, visualized, modified, and used in several ways
(for display, search, ...).

Features
--------

- Supported formats: plain text, ALTO
- Resource page block rendering textual representation as plain text
- Automated text extraction for .pdf, .odt., .docx, .doc and .png files
- Integration with the `Solr
  <https://github.com/biblibre/omeka-s-module-Solr>`__ module (makes the text
  content searchable)
- Text documents are compressed using zlib to save disk space
- Public/private visibility for each textual representation
- Batch removal and batch extraction of textual representations for items and
  media
- Admin advanced search filter on the presence of textual representations
- REST API, and extension points for other modules (formats, ingesters,
  extractors)

.. toctree::
   :maxdepth: 2
   :caption: User documentation

   requirements
   textual-representations
   managing-representations
   automated-extraction
   batch-edit
   mediatext-search
   public-display
   configuration

.. toctree::
   :maxdepth: 2
   :caption: Developer documentation

   developer/rest-api
   developer/extending

