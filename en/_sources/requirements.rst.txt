Requirements
============

- Omeka S ≥ 4.2.0
- PHP extension zlib

If automated text extraction is needed, the following programs are required:

- ``pdftotext`` (for .pdf files)
- ``odt2txt`` (for .odt files)
- ``docx2txt`` (for .docx files)
- ``catdoc`` (for .doc files)
- ``tesseract`` (for .png files)

None of these programs is mandatory. Each one is only needed for the file type
it handles. See :doc:`automated-extraction` for more details.

Installing the extraction programs
----------------------------------

On Debian and Ubuntu, all programs can be installed with:

.. code-block:: sh

    apt install poppler-utils odt2txt docx2txt catdoc tesseract-ocr

``tesseract-ocr`` only installs the English language data. Additional
languages can be installed with the corresponding ``tesseract-ocr-<lang>``
packages (for instance ``tesseract-ocr-fra`` for French).

The programs are looked up in the ``PATH`` of the user that runs Omeka S (for
instance ``www-data``), using ``command -v``. If a program is installed in a
non-standard location, make sure that this location is in the ``PATH`` of that
user.

Installation
------------

Install the module like any other Omeka S module. See `Adding modules to Omeka
S <https://omeka.org/s/docs/user-manual/modules/#adding-modules-to-omeka-s>`__.

Installing the module creates a database table named
``mediatext_representation``. Uninstalling the module deletes this table, and
therefore **all textual representations**.
