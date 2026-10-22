Extending Media Text
====================

Other modules can extend Media Text by providing new formats, ingesters and
extractors. Each of them is a plugin, registered in the module configuration
(``config/module.config.php``) under a dedicated key, using the usual
``invokables`` and ``factories`` sections.

.. list-table::
   :header-rows: 1
   :widths: 25 35 40

   * - Plugin
     - Configuration key
     - Interface
   * - Format adapter
     - ``mediatext_format_adapters``
     - ``MediaText\Format\Adapter\FormatAdapterInterface``
   * - Ingester
     - ``mediatext_representation_ingesters``
     - ``MediaText\Representation\Ingester\RepresentationIngesterInterface``
   * - Extractor
     - ``mediatext_representation_extractors``
     - ``MediaText\Representation\Extractor\RepresentationExtractorInterface``

The key used to register a plugin is its name. It must be the same as the
value returned by its ``getName()`` method.

Format adapters
---------------

A format adapter validates the content of a representation, and converts it
to plain text (for display and indexing).

.. code-block:: php

    'mediatext_format_adapters' => [
        'invokables' => [
            'tei' => \MyModule\MediaText\Format\TeiAdapter::class,
        ],
    ],

.. code-block:: php

    namespace MyModule\MediaText\Format;

    use MediaText\Format\Adapter\AbstractFormatAdapter;
    use Omeka\Stdlib\ErrorStore;

    class TeiAdapter extends AbstractFormatAdapter
    {
        public function getName(): string
        {
            return 'tei';
        }

        public function getLabel(): string
        {
            return 'TEI'; // @translate
        }

        public function validate(string $representation, ErrorStore $errorStore): void
        {
            // Add errors to $errorStore to prevent the representation from
            // being saved
            if (trim($representation) === '') {
                $errorStore->addError('', 'Representation is empty'); // @translate
            }
        }

        public function getPlainText(string $representation): string
        {
            // Convert $representation to plain text
        }
    }

``AbstractFormatAdapter`` implements ``getSortableString()`` (formats are
sorted by label in select elements).

Ingesters
---------

An ingester provides a way to create a textual representation from the media
edit form (and from the API, with ``o:ingester``). Media Text provides two
ingesters: ``textarea`` and ``upload``. Each ingester appears as a button in
the **Import textual representation** section of the sidebar.

.. code-block:: php

    namespace MyModule\MediaText\Ingester;

    use Laminas\View\Renderer\PhpRenderer;
    use MediaText\Entity\Representation;
    use MediaText\Representation\Ingester\AbstractRepresentationIngester;
    use Omeka\Api\Request;
    use Omeka\Stdlib\ErrorStore;

    class Url extends AbstractRepresentationIngester
    {
        public function getName(): string
        {
            return 'url';
        }

        public function getLabel(): string
        {
            return 'URL'; // @translate
        }

        public function form(PhpRenderer $view): string
        {
            // Title, type and format fields
            $form = parent::form($view);

            $url = new \Laminas\Form\Element\Url('o:mediatext_representation[__index__][url]');
            $url->setLabel('URL'); // @translate
            $form .= $view->formRow($url);

            return $form;
        }

        public function ingest(Representation $representation, Request $request, ErrorStore $errorStore): void
        {
            $data = $request->getContent();
            if (empty($data['url'])) {
                $errorStore->addError('url', 'No URL was specified'); // @translate
                return;
            }

            $representation->setContent(file_get_contents($data['url']));
            $representation->setSource($data['url']);
        }
    }

Notes:

- ``form()`` returns the HTML of the form fields. Field names must be of the
  form ``o:mediatext_representation[__index__][...]``; ``__index__`` is
  replaced by the index of the representation in JavaScript.
  ``AbstractRepresentationIngester::form()`` returns the **Title**, **Type**
  and **Format** fields.
- ``ingest()`` must call ``$representation->setContent()``, and can call
  ``$representation->setSource()``. The request data contains the fields of
  the form for this representation only. Uploaded files are available with
  ``$request->getFileData()``.
- Content validation and compression are done by Media Text after
  ``ingest()``.

Extractors
----------

An extractor creates textual representations from a media automatically.

.. code-block:: php

    'mediatext_representation_extractors' => [
        'factories' => [
            'myocr' => \MyModule\Service\MediaText\MyOcrFactory::class,
        ],
    ],

The interface requires the following methods:

``getName(): string`` and ``getLabel(): string``
    Name and human-readable label. The label is used in the default title of
    extracted representations ("Extracted by <label>").

``isAvailable(): bool``
    Whether the extractor can be used on this server (for instance, whether
    the required program is installed). Unavailable extractors are hidden from
    the batch edit form and disabled in the media edit form.

``canExtractFrom(MediaRepresentation $media): bool``
    Whether the extractor supports this media (usually based on its media
    type).

``getRepresentations(MediaRepresentation $media): array``
    Returns an array of new ``MediaText\Entity\Representation`` objects. Each
    one must have at least a type, a format and a content (set with
    ``setContent()``).

Media Text then takes care of the rest: attaching the representations to the
media, setting their title (if empty), position, timestamps and "extracted"
flag, and compressing the content.

For extractors working on the original file of the media, extend
``MediaText\Representation\Extractor\AbstractFileRepresentationExtractor`` and
implement ``getRepresentationsFromFile(string $filepath): array``. The
abstract class retrieves a local path of the file (downloading it if the file
store is not local) and sets the source of the representations to the source
of the media. The file store and the downloader must be injected in the
factory:

.. code-block:: php

    namespace MyModule\Service\MediaText;

    use Laminas\ServiceManager\Factory\FactoryInterface;
    use MyModule\MediaText\Extractor\MyOcr;
    use Psr\Container\ContainerInterface;

    class MyOcrFactory implements FactoryInterface
    {
        public function __invoke(ContainerInterface $services, $requestedName, ?array $options = null)
        {
            $service = new MyOcr($services->get('Omeka\Cli'));
            $service->setDownloader($services->get('Omeka\File\Downloader'));
            $service->setFileStore($services->get('Omeka\File\Store'));

            return $service;
        }
    }

See ``src/Representation/Extractor/Tesseract.php`` for a complete example.

PHP API
-------

``MediaText\Api\Representation\RepresentationRepresentation`` provides the
following methods, useful in themes and modules:

.. list-table::
   :header-rows: 1
   :widths: 30 70

   * - Method
     - Description
   * - ``media()``
     - The media (``MediaRepresentation``)
   * - ``title()``, ``type()``, ``format()``, ``source()``, ``position()``
     - Properties of the representation
   * - ``typeLabel()``, ``formatLabel()``
     - Human-readable labels for the type and the format
   * - ``isPublic()``, ``isExtracted()``
     - Visibility and "automatically extracted" flag
   * - ``content()``
     - The content, uncompressed, in its original format
   * - ``contentAsPlainText()``
     - The content converted to plain text by the format adapter
   * - ``created()``, ``modified()``
     - Timestamps (``DateTime``)

Example, in a theme's media page:

.. code-block:: php

    <?php
    $representations = $this->api()->search('mediatext_representations', [
        'media_id' => $media->id(),
        'sort_by' => 'position',
        'sort_order' => 'asc',
    ])->getContent();
    ?>
    <?php foreach ($representations as $representation): ?>
        <h3><?= $this->escapeHtml($representation->title()) ?></h3>
        <pre><?= $this->escapeHtml($representation->contentAsPlainText()) ?></pre>
    <?php endforeach; ?>
