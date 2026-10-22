Managing textual representations
================================

Textual representations are managed from the media pages of the admin
interface.

Viewing the textual representations of a media
----------------------------------------------

On the page of a media, a tab named **Textual representations** lists all the
textual representations of the media, in order. For each representation, the
list shows:

- its title, linked to the representation page,
- its type and its format,
- the mention "Automatically extracted" if it was created by an extractor,
- a "private" icon if it is private.

Two actions are available for each representation:

- **Edit** opens the representation edit page,
- **Details** opens a sidebar with the ID, type, format, source and content of
  the representation.

Representation page
^^^^^^^^^^^^^^^^^^^

The representation page shows the ID, type, format and source of the
representation, along with its content. The content is displayed twice, side by
side: as stored (for instance ALTO XML), and converted to plain text. This
allows to check what will be displayed on public pages and indexed by search
engines.

Adding textual representations
------------------------------

1. Edit the media, and click on the **Textual representations** tab.
2. In the sidebar on the right, under **Import textual representation**, click
   on one of the following buttons:

   - **Text area**: type or paste the content directly in a text field,
   - **Upload**: upload a file containing the content.

3. Fill in the form that appears:

   - **Title** (required),
   - **Type** (required): Transcription, Transliteration, Translation or
     Other,
   - **Format** (required): Plain text or ALTO,
   - **Content** or **Upload file** depending on the chosen method.

4. Click on **Save**.

Several representations can be added at once by clicking several times on the
buttons. A new representation that has not been saved yet can be removed with
its **Remove representation** icon.

When using **Upload**, the content of the file is copied in the database (the
file itself is not kept) and the file name is stored as the source of the
representation.

The content is validated according to its format before being saved (see
:ref:`formats`). If the validation fails, an error
message is displayed and the media is not saved.

Extracting textual representations
----------------------------------

The same sidebar contains a section named **Extract textual representation
from media**. It lists the extractors that are able to handle the media file.
Check one or more extractors and click on **Save** to create new textual
representations from the media file.

See :doc:`automated-extraction` for more details.

Reordering textual representations
----------------------------------

On the media edit page, drag and drop representations using their handle, then
click on **Save**. The order is used in the admin interface.

Changing the visibility
-----------------------

On the media edit page, click on the visibility icon (**Make private** /
**Make public**) of a representation, then click on **Save**. See
:ref:`visibility`.

Editing a textual representation
--------------------------------

Click on the **Edit** icon of a representation (from the media page, the media
edit page or the representation page). The edit form allows to change the
title, the type, the format and the content of the representation.

The content is validated according to the selected format before being saved.

Deleting textual representations
--------------------------------

On the media edit page, click on the **Delete** icon of a representation. The
representation is marked for removal, and can be restored with the **Restore**
icon until the media is saved. It is deleted when clicking on **Save**.

To delete textual representations of many media at once, see
:doc:`batch-edit`.

Permissions
-----------

- Everybody can view textual representations, according to their visibility
  (see :ref:`visibility`).
- Authors, reviewers, editors, site administrators and global administrators
  can create, edit and delete textual representations. Note that, as for any
  other change on a media, they need to be allowed to edit the media.
