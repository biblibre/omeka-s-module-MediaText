Batch edit
==========

Media Text adds a section named **Media Text** to the batch edit form of items
and media in the admin interface. It allows to remove and to extract textual
representations of many resources at once.

To use it, browse items or media, select resources (or choose to edit all
resources), and choose **Edit selected** (or **Edit all**). The
`batch edit <https://omeka.org/s/docs/user-manual/content/items/#batch-edit-items>`__
feature of Omeka S is available to editors, site administrators and global
administrators.

Options
-------

Remove textual representation
   - *Do not remove representations* (default)
   - *Remove all representations*: deletes all textual representations
   - *Remove automatically extracted representations only*: deletes only the
     representations created by an extractor (see
     :doc:`automated-extraction`). Representations added manually are kept.

Extract textual representation
   A list of the available extractors (only extractors whose program is
   installed on the server are listed). Select one or more extractors to
   create new textual representations. An extractor is only applied to media
   whose file type it supports; other media are left unchanged.

   If no extractor is available, this field is not displayed.

When both options are used, removal is done first, then extraction. For
instance, to re-run text extraction and replace previous results, select
*Remove automatically extracted representations only* and the extractors to
use.

When editing items, the options are applied to all the media of each selected
item.

.. note::

   Text extraction can take a long time. When using **Edit all**, Omeka S
   processes the batch edit in a background job: check its status in
   **Jobs** in the admin menu. When using **Edit selected**, prefer small
   selections to avoid timeouts.
