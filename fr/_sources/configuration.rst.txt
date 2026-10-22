Configuration
=============

The module configuration form is available in the admin interface, in
**Modules**, by clicking on the **Configure** button of Media Text.

Text compression level
----------------------

Media Text compresses the content of textual representations to reduce the
space taken in the database. This setting allows to change the zlib
compression level:

- *Default compression level* (default): the zlib default, currently
  equivalent to 6,
- *0 (no compression)*,
- *1 (fastest)* to *9 (best compression)*.

A higher level saves more disk space but takes more time to compress. The
decompression time is not significantly affected.

The new level is only applied to textual representations created or modified
after the change. To apply it to existing representations, use the
compression background job.

Start compression background job
--------------------------------

When this box is checked, saving the configuration form starts a background
job that recompresses all existing textual representations with the selected
compression level. A link to the job details is displayed.

The job logs its progress every 100 representations. It can be stopped from
the job details page; representations already processed keep their new
compression level.
