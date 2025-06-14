<?php

return [
    /**
     * The book title.
     */
    'title' => 'Ibis Next: create your eBooks from Markdown',

    /**
     * The author name.
     */
    'author' => 'Roberto B.',

    /**
     * The list of fonts to be used in the different themes.
     */
    'fonts' => [
        'georgia-pro' => [
            'normal' => 'assets/fonts/GeorgiaPro/GeorgiaPro-Regular.ttf',
            'bold' => 'assets/fonts/GeorgiaPro/GeorgiaPro-Bold.ttf',
            'italic' => 'assets/fonts/GeorgiaPro/GeorgiaPro-Italic.ttf',
            'bold_italic' => 'assets/fonts/GeorgiaPro/GeorgiaPro-BoldItalic.ttf',
        ],
        'open-sans' => [
            'normal' => 'assets/fonts/OpenSans/OpenSans-Regular.ttf',
            'bold' => 'assets/fonts/OpenSans/OpenSans-Bold.ttf',
            'italic' => 'assets/fonts/OpenSans-Italic.ttf',
            'bold_italic' => 'assets/fonts/OpenSans-BoldItalic.ttf',
        ],
    ],

    'language' => 'en',

    /**
     * Document Dimensions.
     */
    'document' => [
        'format' => [210, 297],
		'margin_left'   => 25,
		'margin_right'  => 25,
		'margin_top'    => 20,
		'margin_bottom' => 25,
    ],

    /**
     * Table of Contents Levels
     */
    'toc_levels' => [
        'H1' => 0,
        'H2' => 0,
        'H3' => 1,
    ],

    /**
     * Cover photo position and dimensions
     */
    'cover' => [
        'position' => 'position: absolute; left:0; right: 0; top: -.2; bottom: 0;',
        'dimensions' => 'width: 210mm; height: 297mm; margin: 0;',
        'image' => 'cover-ibis.webp',
    ],

    /**
     * Page ranges to be used with the sample command.
     */
    'sample' => [
        [1, 7],
        [15, 15],
    ],

    /**
     * default commonmark
     */
    'configure_commonmark' => [
    ],
    /**
     * A notice printed at the final page of a generated sample.
     */
    'sample_notice' => 'This is a sample from "Ibis Next: create your eBooks with Markdown" by Roberto Butti. <br>
                        For more information, <a href="https://github.com/Hi-Folks/ibis-next">Click here</a>.',

    /**
     * CSS inline style for the page header.
     * If you want to skip header, comment the line
     */
    'header' => 'font-style: italic; text-align: right; border-bottom: solid 1px #808080;',

    /**
     * List of the Markdown files.
     * If the `md_file_list` is not set (default)
     * all the markdown files in the content directory
     * will be loaded.
     * If you need to select a subset of markdown files
     * for creating PDF or EPUB or HTML you can list here
     * the files. You need to set the filename in the
     * content directory. The fieldname should include the
     * extension.
     */
    /*
        'md_file_list' => [
            'routing.md',
            'artisan.md',
            'console-tests.md',
        ],
    */
];
