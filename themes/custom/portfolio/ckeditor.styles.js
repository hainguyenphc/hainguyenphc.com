/*
Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/*
 * This file is used/requested by the 'Styles' button.
 * The 'Styles' button is not enabled by default in DrupalFull and DrupalFiltered toolbars.
 */
if(typeof(CKEDITOR) !== 'undefined') {
    CKEDITOR.addStylesSet( 'drupal',
    [
            /* Block Styles */

            // These styles are already available in the "Format" drop-down list, so they are
            // not needed here by default. You may enable them to avoid placing the
            // "Format" drop-down list in the toolbar, maintaining the same features.

            { name : 'Paragraph'    , element : 'p' },
            { name : 'Heading 2'    , element : 'h2' },
            { name : 'Heading 3'    , element : 'h3' },
            { name : 'Heading 4'    , element : 'h4' },
            { name : 'Heading 5'    , element : 'h5' },
            { name : 'Heading 6'    , element : 'h6' },
            // { name : 'Special Title'        , element : 'h3', styles : { 'color' : 'Red' } },
            {name : 'Headline Type', element: 'p', attributes : { 'class' : 'headline-type'} },

            /* Inline Styles */
            // { name : 'Marker: Yellow' , element : 'span', styles : { 'background-color' : 'Yellow' } },
            // { name : 'Big'        , element : 'big' },
            { name : 'Small Type', element: 'span', attributes : { 'class' : 'small-type'} },
            { name : 'Highlight Type', element: 'span', attributes : { 'class' : 'highlight-type'} },
            { name : 'Highlight Box', element: 'div', attributes : { 'class' : 'highlight-box'} },
            { name : 'Lead Text Style', element: 'span', attributes : { 'class' : 'lead'} },
            { name : 'Small Type', element: 'span', attributes : { 'class' : 'small-type'} },
            { name : 'Primary Button', element: 'a', attributes : { 'class' : 'btn-primary'} },
            { name : 'Secondary Button', element: 'a', attributes : { 'class' : 'btn-secondary'} },
            { name : 'Red Button', element: 'a', attributes : { 'class' : 'btn-red'} },
            { name : 'Blue border Button', element: 'a', attributes : { 'class' : 'border-button-blue-white'} },
            { name : 'Italic Underline Link', element: 'a', attributes : { 'class' : 'italic-underline-link'} },
            { name : 'No Underline Link', element: 'a', attributes : { 'class' : 'title-link-no-underline'} },
            { name : 'White', element: 'span', attributes : { 'class' : 'white-link'} },
            { name : 'Black', element: 'span', attributes : { 'class' : 'black'} },
            { name : 'Light Blue', element: 'span', attributes : { 'class' : 'light-blue'} },
            { name : 'Float Right', element: 'span', attributes : { 'class' : 'float-right-txt'} },
            { name : 'Float Left', element: 'span', attributes : { 'class' : 'float-left'} },
            { name : 'Display Inline', element: 'span', attributes : { 'class' : 'display-inline'} },
            { name : 'Lead Secondary', element: 'span', attributes : { 'class' : 'lead-secondary'} },
            { name : 'No Wrap', element: 'span', attributes : { 'class' : 'nowrap'} },




            /* Object Styles */
            {
                    name : 'Float Image Left',
                    element : 'img',
                    attributes :
                    {
                        'class' : 'inline-image-left'
                    }
            },
            {
                    name: 'Float Image Right',
                    element : 'img',
                    attributes :
                    {
                        'class' : 'inline-image-right'
                    }
            },
            {
                    name: 'Centered Image',
                    element : 'img',
                    attributes :
                    {
                        'class' : 'inline-image-center'
                    }
            }
    ]);
}
