Public display
==============

Textual representations can be displayed on the public media pages of a site
using a resource page block.

Resource page block
-------------------

Media Text provides a resource page block named **Textual representations as
plain text**, compatible with media pages.

To add it:

1. In the admin interface, edit the site, and go to **Theme** (or **Resource
   pages**, depending on the theme and the Omeka S version).
2. In the media page configuration, add the **Textual representations as plain
   text** block to a region.
3. Save.

See `Configure resource pages
<https://omeka.org/s/docs/user-manual/sites/site_theme/#configure-resource-pages>`__
in the Omeka S user manual.

The block displays, for each textual representation of the media visible to
the visitor (see :ref:`visibility`), its title and its content converted to
plain text (see :ref:`formats`). Nothing is displayed if the media has no
visible textual representation.

Styling
-------

The block is rendered as an HTML definition list:

.. code-block:: html

    <dl class="mediatext-text">
        <div class="property">
            <dt>Title of the representation</dt>
            <dd class="value">Content as plain text</dd>
        </div>
        ...
    </dl>

The module stylesheet (``asset/css/mediatext-site.css``) preserves line breaks
of the text. It can be overridden in the theme.
