Search
======

Admin advanced search
---------------------

Media Text adds a field named **Search by textual representations presence**
to the advanced search form of items and media in the admin interface. It has
the following options:

- *Has textual representations*: only returns items (or media) that have at
  least one textual representation (for items: at least one media of the item
  has a textual representation),
- *Has no textual representations*: only returns items (or media) without any
  textual representation.

Full-text search with Solr
--------------------------

When the `Solr <https://github.com/biblibre/omeka-s-module-Solr>`__ module
(and the `Search <https://github.com/biblibre/omeka-s-module-Search>`__ module
it depends on) is installed, the content of textual representations can be indexed, making it
searchable.

Media Text adds a new source field to the Solr module, under **Media**, named
**Media text representation content (plain text)**. It contains the content of
all the textual representations of all the media of an item, converted to
plain text (see :ref:`formats`).

To use it:

1. Go to the Solr module configuration, and edit the mappings of the Solr
   index for items.
2. Add a new mapping using the source **Media › Media text representation
   content (plain text)**, and choose a Solr field suitable for full text (for
   instance a ``*_txt`` field).
3. Reindex the items.

See the documentation of the Solr module for more details.

.. note::

   Only items are supported. Textual representations are not added to
   indexed media.

.. note::

   Editing a textual representation from its own edit page does not modify
   the media nor the item, so the change is not detected by the Search
   module. Save the item (or rebuild the index) to update the indexed text.
