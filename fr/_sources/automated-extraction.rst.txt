Automated text extraction
=========================

Media Text can create textual representations automatically by extracting the
text from media files, using external programs called *extractors*.

Available extractors
--------------------

.. list-table::
   :header-rows: 1
   :widths: 20 20 60

   * - Extractor
     - File type
     - Media type
   * - pdftotext
     - .pdf
     - ``application/pdf``
   * - odt2txt
     - .odt
     - ``application/vnd.oasis.opendocument.text``
   * - docx2txt
     - .docx
     - ``application/vnd.openxmlformats-officedocument.wordprocessingml.document``
   * - catdoc
     - .doc
     - ``application/msword``
   * - tesseract
     - .png
     - ``image/png``

An extractor is *available* if the corresponding program is installed on the
server (see :doc:`requirements`). An extractor can only be used on media that
have an original file with a supported media type.

``tesseract`` is an OCR (optical character recognition) engine. It is run with
its default settings, which means that it uses the English language data.

Extracted representations
-------------------------

Each extractor creates one textual representation with the following
properties:

- **Title**: "Extracted by <extractor>" (for instance "Extracted by pdftotext")
- **Type**: Transcription
- **Format**: Plain text
- **Visibility**: Public
- **Source**: the source of the media (usually the name of the original file,
  or its URL)
- **Automatically extracted**: yes

Extracted representations are added after the existing representations of the
media. They can be edited, reordered and deleted like any other representation.

Extracting the text of a media file is done synchronously, during the save of
the media. It can be slow for large files, especially with ``tesseract``.

If the media files are not stored locally (for instance with a module storing
files on Amazon S3), they are downloaded to a temporary file before extraction.

When does extraction happen?
----------------------------

On media creation
^^^^^^^^^^^^^^^^^

When a media is created (for instance when adding a file to an item, or during
an import), **all available extractors** that support the media file are run
automatically.

On media edition
^^^^^^^^^^^^^^^^

Edit the media, click on the **Textual representations** tab, and in the
sidebar, under **Extract textual representation from media**, check the
extractors to use. Then click on **Save**.

Only the extractors that support the media file are listed. Extractors whose
program is not installed are listed but disabled, with the mention "(not
available)".

Extraction does not replace existing representations: running the same
extractor twice creates two representations. To replace extracted
representations, delete them first (see :doc:`batch-edit`).

In batch
^^^^^^^^

Extractors can be run on many items or media at once using the batch edit
form. See :doc:`batch-edit`.
