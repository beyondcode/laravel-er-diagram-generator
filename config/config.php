<?php

return [

    /*
     * All models in these directories will be scanned for ER diagram generation.
     * The directories will not be scanned recursively, so be sure to add all
     * directories that contain your models.
     */
    'directories' => [
        // app_path('models'),
    ],

    /*
     * The generator will automatically try to look up the model specific columns
     * and add them to the generated output. If you do not wish to use this
     * feature, you can disable it here.
     */
    'use_db_schema' => true,

    /*
     * These colors will be used in the table representation for each entity in
     * your graph.
     */
    'table' => [
        'header_background_color' => '#d3d3d3',
        'header_font_color' => '#333333',
        'row_background_color' => '#ffffff',
        'row_font_color' => '#333333',
    ],

    /*
     * Here you can define all the available Graphviz attributes that should be applied to your graph,
     * to its nodes and to the edge (the connection between the nodes). Depending on the size of
     * your diagram, different settings might produce better looking results for you.
     *
     * See http://www.graphviz.org/doc/info/attrs.html#d:label for a full list of attributes.
     */
    'graph' => [
        'style' => 'filled',
        'bgcolor' => '#F7F7F7',
        'fontsize' => 12,
        'labelloc' => 't',
        'concentrate' => true,
        'splines' => 'polyline',
        'overlap' => false,
        'nodesep' => 1,
        'rankdir' => 'LR',
        'pad' => 0.5,
        'ranksep' => 2,
        'esep' => true,
        'fontname' => 'Helvetica Neue'
    ],

    'node' => [
        'margin' => 0,
        'shape' => 'rectangle',
        'fontname' => 'Helvetica Neue'
    ],

    'edge' => [
        'color' => '#003049',
        'penwidth' => 1.8,
        'fontname' => 'Helvetica Neue'
    ],

    'relations' => [
        'HasOne' => [
            'dir' => 'both',
            'color' => '#D62828',
            'arrowhead' => 'tee',
            'arrowtail' => 'none',
        ],
        'BelongsTo' => [
            'dir' => 'both',
            'color' => '#F77F00',
            'arrowhead' => 'tee',
            'arrowtail' => 'crow',
        ],
        'HasMany' => [
            'dir' => 'both',
            'color' => '#FCBF49',
            'arrowhead' => 'crow',
            'arrowtail' => 'none',
        ],
    ]

];