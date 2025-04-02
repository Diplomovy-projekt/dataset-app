<x-app-layout>
    <div class="py-6">
    <x-misc.header title="Extending Support for New Annotation Formats" align="center"/>

    <div class="space-y-6 mt-6">
        <div class=" p-4 border border-slate-700 rounded-lg">
            <h2 class="text-xl font-bold text-gray-200">Overview of Import/Export Flow</h2>
            <p class="text-gray-400 mt-2">Our system transforms annotations into a specific internal format, storing them in a database. During export, we convert them back into the selected format.</p>
        </div>

        <div class="border border-slate-700 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-200">Import Process</h3>
            <p class="text-gray-400">We expect a ZIP file with a predefined folder structure corresponding to the annotation format. The system does <strong>not</strong> support datasets split into train/valid/test folders.</p>
            <ol class="list-decimal list-inside text-gray-300 space-y-2 mt-2">
                <li>Parse and map the ZIP file:
                    <ul class="list-disc list-inside ml-4">
                        <li>Store images in the filesystem and generate thumbnails.</li>
                    </ul>
                </li>
                <li>Convert annotations into our internal format and save them in the database.</li>
            </ol>
        </div>

        <div class="border border-slate-700 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-200">Export Process</h3>
            <ul class="list-disc list-inside text-gray-300 space-y-2">
                <li>Annotation files are generated from database records.</li>
                <li>The export process handles large datasets in chunks, streaming data into files.</li>
            </ul>
        </div>

        <x-misc.header title="Adding Support for a New Annotation Format"/>

        <div class="border border-slate-700 rounded-lg p-4">
            <p class="text-gray-400">To integrate a new annotation format, follow these steps:</p>
            <ol class="list-decimal list-inside text-gray-300 space-y-2 mt-2">
                <li>Use our Artisan command to generate necessary files.</li>
                <li>Implement required mapping and validation methods, and configs.</li>
                <li>Define concrete implementations for the abstract methods in the base classes.</li>
                <li>Review the base classes for method documentation.</li>
                <li>Add the format name to <code class="bg-gray-700 p-1 rounded">ANNOTATION_FORMATS_INFO</code> in <code class="bg-gray-700 p-1 rounded">app/Configs/AppConfig.php</code>:</li>
            </ol>
            <pre class="bg-gray-900 p-4 rounded-lg mt-4 text-gray-300">
<code>public const ANNOTATION_FORMATS_INFO = [
        'pascalvoc' => [
            'name' => 'PascalVOC',
            'extension' => 'xml',
            ],
    ]</code>
            </pre>
        </div>

        <div class="border border-slate-700 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-200">Generating the Required Files</h3>
            <pre class="bg-gray-900 p-4 rounded-lg mt-2 text-gray-300"><code>php artisan make:format {annotation_format_name}</code></pre>
            <p class="text-gray-400 mt-2">To remove a format, use: (cannot be undone)</p>
            <pre class="bg-gray-900 p-4 rounded-lg mt-2 text-gray-300"><code>php artisan make:format {annotation_format_name} --delete</code></pre>
        </div>

        <div class="border border-slate-700 rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-200">Example:</h4>
            <pre class="bg-gray-900 p-4 rounded-lg mt-2 text-gray-300"><code>php artisan make:format paligemma</code></pre>
        </div>

        <div class="border border-slate-700 rounded-lg p-4">
            <h4 class="text-lg font-semibold text-gray-200">Example output:</h4>
            <pre class="bg-gray-900 p-4 rounded-lg mt-2 text-gray-300"><code>$ php artisan make:format paligemma

Created: app/Configs/Annotations/PaligemmaConfig.php
Created: app/ExportService/Mappers/ToPaligemma.php
Created: app/ImportService/Mappers/FromPaligemma.php
Created: app/ImportService/Validators/Paligemma/PaligemmaAnnotationValidator.php
Created: app/ImportService/Validators/Paligemma/PaligemmaZipValidator.php
Created: resources/views/components/zip-format-info/paligemma.blade.php
Format Paligemma created successfully!

Note: Implement required methods in the following base classes:
 - **BaseAnnotationConfig**: Define required constants for annotation structure.
 - **BaseToMapper**: Implement methods for mapping **to** the new format.
 - **BaseFromMapper**: Implement methods for mapping **from** the new format.
 - **Base Validators**: Implement validation logic for annotation files.
 - **View for ZIP structure info**: Provide format-specific details.</code></pre>
        </div>
        <x-misc.header title="Example: Implementing an Import Mapper"/>

        <div class="border border-slate-700 rounded-lg p-4 overflow-auto">
            <pre class="bg-gray-900 p-4 rounded-lg text-gray-300">
<code>&lt;?php

namespace App\ImportService\Mappers;

use App\ImportService\Mappers\BaseFromMapper;

/**
 * Handles mapping from Paligemma format to internal representation.
 * @see \App\ImportService\Mappers\BaseFromMapper for required method details.
 */
class FromPaligemma extends BaseFromMapper
{
    /**
     * Parses annotation data.
     * @see \App\ImportService\Mappers\BaseFromMapper::parse
     */
    public function parse(string $folderName, $annotationTechnique): \App\Utils\Response
    {
        // Implement parsing logic
    }

    /**
     * Converts bounding box data.
     * @see \App\ImportService\Mappers\BaseFromMapper::transformBoundingBox
     */
    public function transformBoundingBox(array $bbox, array $imgDims = null): array
    {
        // Implement bounding box transformation
    }

    /**
     * Converts polygon annotation data.
     * @see \App\ImportService\Mappers\BaseFromMapper::transformPolygon
     */
    public function transformPolygon(array $polygonPoints, array $imgDims = null): array
    {
        // Implement polygon transformation
    }

    /**
     * Retrieves class labels from the dataset.
     * @see \App\ImportService\Mappers\BaseFromMapper::getClasses
     */
    public function getClasses($classesSource): array
    {
        // Implement class label extraction
    }
}
</code>
            </pre>
        </div>
    </div>
    </div>
</x-app-layout>
