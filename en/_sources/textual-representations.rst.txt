Textual representations
=======================

A textual representation is a resource attached to a media. It describes the
text contained in the media (whether the media is an image, a PDF, a video, a
sound, or anything else).

It is mostly useful to store transcriptions of a media, but it can be useful to
store translations or transliterations.

Textual representations can be stored as plain text or `ALTO
<https://www.loc.gov/standards/alto/>`__.

A media can have several textual representations.

Properties
----------

Each textual representation has the following properties:

Title
    A free-form title (required). Automatically extracted representationsare
    titled "Extracted by <extractor>" (for instance "Extracted by pdftotext").

Type
    One of *Transcription*, *Transliteration*, *Translation* or *Other*.

Format
    The format of the content: *Plain text* or *ALTO*. See `Formats`_.

Content
    The text itself.

Visibility
    Public or private. See `Visibility`_.

Source
    Where the content comes from. It is set automatically to the name of the
    uploaded file when using the *Upload* method, or to the source of the media
    when the representation is automatically extracted.

Automatically extracted
    Whether the representation was created by an extractor (see
    :doc:`automated-extraction`). This allows to remove only the extracted
    representations in batch (see :doc:`batch-edit`).

Position
    The order of the representation among the representations of the media.

Created / Modified
    Date of creation and date of last modification.

.. _formats:

Formats
-------

Plain text
^^^^^^^^^^

Any text is accepted. The content is stored and displayed as is.

ALTO
^^^^

`ALTO <https://www.loc.gov/standards/alto/>`__ (Analyzed Layout and Text
Object) is an XML format commonly produced by OCR software. It contains the
text and its position on the page.

When an ALTO representation is saved, the module checks that:

- the content is not empty,
- the content is well-formed XML,
- the root element is ``alto``,
- the root element has a namespace (any ALTO version is accepted).

If one of these checks fails, the representation is not saved and an error is
displayed.

The module is able to convert ALTO to plain text. This plain text version is
used for display on public pages (see :doc:`public-display`) and for indexing
(see :doc:`mediatext-search`). It is built as follows:

- each ``String`` element of a ``TextLine`` is replaced by its ``CONTENT``
  attribute, separated by spaces,
- each ``TextLine`` is put on its own line,
- each ``TextBlock`` is separated from the next one by an empty line.

.. _visibility:

Visibility
----------

Each textual representation can be either public or private. New
representations are public by default.

A textual representation is visible:

- to users who can view all resources (global administrators, site
  administrators, editors and reviewers), whatever its visibility,
- to the owner of the media it is attached to, whatever its visibility,
- to everybody else (including anonymous visitors), if it is public **and** if
  the media it is attached to is visible to them.

In other words, a public representation of a private media is not visible to
the public.

Storage
-------

The content of textual representations is compressed in the database using
zlib (gzip format) to save disk space. The compression level can be configured
(see :doc:`configuration`).

Representations are attached to a media: when a media is deleted, all its
textual representations are deleted too.
