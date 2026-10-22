REST API
========

Media Text adds a new API resource named ``mediatext_representations``.
It can be used through the Omeka S `REST API
<https://omeka.org/s/docs/developer/api/rest_api/>`__ and the PHP API
(``$api->search('mediatext_representations', ...)``).

The usual Omeka S rules apply: authentication with key identity and key
credential is needed to create, update and delete representations, and to read
private ones (see :ref:`visibility`).

Representation JSON-LD
----------------------

``GET /api/mediatext_representations/:id``

.. code-block:: json

    {
        "@context": "...",
        "@id": "https://example.org/api/mediatext_representations/1",
        "@type": "o-module-mediatext:Representation",
        "o:id": 1,
        "o:media": {
            "@id": "https://example.org/api/media/42",
            "o:id": 42
        },
        "o:type": "transcription",
        "o:format": "plain_text",
        "o:content": "The full text",
        "o:created": {
            "@value": "2026-07-28T10:00:00+00:00",
            "@type": "http://www.w3.org/2001/XMLSchema#dateTime"
        },
        "o:modified": {
            "@value": "2026-07-28T10:00:00+00:00",
            "@type": "http://www.w3.org/2001/XMLSchema#dateTime"
        }
    }

``o:content`` is returned uncompressed, in its original format (not converted
to plain text).

Values of ``o:type``: ``transcription``, ``transliteration``, ``translation``,
``other``.

Values of ``o:format``: ``plain_text``, ``alto`` (and any format added by
other modules, see :doc:`extending`).

Media JSON-LD
^^^^^^^^^^^^^

The JSON-LD of a media that has textual representations contains an
additional key, ``o-module-mediatext:representations``, with references to
these representations:

.. code-block:: json

    {
        "@id": "https://example.org/api/media/42",
        "o:id": 42,
        "...": "...",
        "o-module-mediatext:representations": [
            {
                "@id": "https://example.org/api/mediatext_representations/1",
                "o:id": 1
            }
        ]
    }

Search
------

``GET /api/mediatext_representations``

Available query parameters (in addition to the standard ones like ``page``,
``per_page``, ``sort_by``, ``sort_order``):

.. list-table::
   :header-rows: 1
   :widths: 20 80

   * - Parameter
     - Description
   * - ``media_id``
     - ID of the media
   * - ``title``
     - Exact title
   * - ``type``
     - Type (``transcription``, ``transliteration``, ...)
   * - ``format``
     - Format (``plain_text``, ``alto``, ...)

``sort_by`` accepts: ``id``, ``title``, ``type``, ``format``, ``position``,
``created``, ``modified``.

Example: all representations of media 42, in order::

    GET /api/mediatext_representations?media_id=42&sort_by=position&sort_order=asc

Items and media search
^^^^^^^^^^^^^^^^^^^^^^

The ``mediatext_has_textual_representations`` parameter (``1`` or ``0``) can be
passed to ``/api/items`` and ``/api/media`` to filter resources by presence of
textual representations. It is only taken into account for admin requests (see
:doc:`../mediatext-search`).


.. _create:

Create
------

``POST /api/mediatext_representations``

Representations are created through an *ingester* (see :doc:`extending`),
given by ``o:ingester``. With the ``textarea`` ingester, the content is given
in the ``content`` key:

.. code-block:: json

    {
        "o:media": {"o:id": 42},
        "o:title": "Transcription",
        "o:type": "transcription",
        "o:format": "plain_text",
        "o:is_public": true,
        "o:ingester": "textarea",
        "content": "The full text"
    }

With the ``upload`` ingester, send a ``multipart/form-data`` request with the
JSON data in the ``data`` field, the file in ``upload_file[<index>]``, and
the same ``<index>`` in the ``file_index`` key of the JSON data. The file
name is stored as source, unless ``o:source`` is given.

The content is validated according to its format; validation errors are
returned as usual by the API.

.. note::

   A representation created this way gets the position ``0``. Use the media
   update described below to control the order of representations.

Update
------

``PATCH /api/mediatext_representations/:id`` (or ``PUT``)

Accepted keys: ``o:title``, ``o:type``, ``o:format``, ``o:is_public``,
``o:content``. The content is validated according to the format.

Delete
------

``DELETE /api/mediatext_representations/:id``

Managing representations through the media
------------------------------------------

Representations can also be managed when updating a media
(``PUT``/``PATCH /api/media/:id``), with the ``o:mediatext_representation``
key. This is what the admin interface uses. When this key is present:

- its items are processed in order and their position is set accordingly,
- an item with an ``o:id`` keeps an existing representation; its
  ``o:is_public`` can be changed,
- an item without ``o:id`` creates a new representation (same keys as for
  :ref:`create`, without ``o:media``),
- **existing representations of the media that are not listed are deleted**.

.. code-block:: json

    {
        "o:mediatext_representation": [
            {"o:id": 3},
            {"o:id": 1, "o:is_public": false},
            {
                "o:title": "Translation",
                "o:type": "translation",
                "o:format": "plain_text",
                "o:ingester": "textarea",
                "content": "..."
            }
        ]
    }

Additional keys are accepted when updating media and items (including batch
updates):

``mediatext_remove_representations``
    ``all`` to remove all representations, ``extracted`` to remove only
    automatically extracted representations. For items, applies to all their
    media.

``mediatext_extractors``
    An array of extractor names (``pdftotext``, ``odt2txt``, ``docx2txt``,
    ``catdoc``, ``tesseract``) to run on the media (for items, on all their
    media). See :doc:`../automated-extraction`.

On media creation, all available extractors are run automatically.
