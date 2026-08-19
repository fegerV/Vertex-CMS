<?php

namespace App\Builder\Config;

/*
 |--------------------------------------------------------------------------
 | Vertex Builder Block Catalog
 |--------------------------------------------------------------------------
 |
 | This catalog is the backend source of truth for block availability,
 | defaults, editor fields and high-level editor metadata. Public rendering
 | still happens through Blade/PHP, while the Vue builder consumes this
 | structured contract through BuilderContractSerializer.
 |
 */

$blocks = array (
  'heading' => 
  array (
    'name' => 'Heading',
    'category' => 'content',
    'icon' => 'type-h2',
    'description' => 'Primary page or section heading.',
    'default' => 
    array (
      'type' => 'heading',
      'settings' => 
      array (
        'level' => 'h2',
        'text' => 'New heading',
        'align' => 'left',
        'color' => '#111827',
        'font_size' => '1.5rem',
        'font_weight' => '600',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'typography-pack' => 
        array (
          'label' => 'Typography',
          'description' => 'Heading copy, semantic level and alignment.',
          'icon' => 'text',
          'fields' => 
          array (
            0 => 'text',
            1 => 'level',
            2 => 'align',
            3 => 'font_size',
          ),
        ),
        'surface-pack' => 
        array (
          'label' => 'Surface',
          'description' => 'Color treatment for the heading.',
          'icon' => 'palette',
          'fields' => 
          array (
            0 => 'color',
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'level' => 
      array (
        'type' => 'select',
        'label' => 'Level',
        'options' => 
        array (
          'h1' => 'H1',
          'h2' => 'H2',
          'h3' => 'H3',
          'h4' => 'H4',
          'h5' => 'H5',
          'h6' => 'H6',
        ),
      ),
      'text' => 
      array (
        'type' => 'text',
        'label' => 'Text',
        'required' => true,
      ),
      'align' => 
      array (
        'type' => 'select',
        'label' => 'Alignment',
        'options' => 
        array (
          'left' => 'Left',
          'center' => 'Center',
          'right' => 'Right',
          'justify' => 'Justify',
        ),
      ),
      'color' => 
      array (
        'type' => 'color',
        'label' => 'Color',
      ),
      'font_size' => 
      array (
        'type' => 'select',
        'label' => 'Font size',
        'options' => 
        array (
          '0.875rem' => 'Small',
          '1rem' => 'Base',
          '1.125rem' => 'Large',
          '1.25rem' => 'Extra large',
          '1.5rem' => 'Display',
          '2rem' => 'Hero',
        ),
      ),
    ),
  ),
  'text' => 
  array (
    'name' => 'Text',
    'category' => 'content',
    'icon' => 'paragraph',
    'description' => 'Rich paragraph text block.',
    'default' => 
    array (
      'type' => 'text',
      'settings' => 
      array (
        'content' => 'Add your text here.',
        'align' => 'left',
        'color' => '#4b5563',
        'font_size' => '1rem',
        'line_height' => '1.6',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'typography-pack' => 
        array (
          'label' => 'Typography',
          'description' => 'Body copy, alignment and readable text scale.',
          'icon' => 'text',
          'fields' => 
          array (
            0 => 'content',
            1 => 'align',
            2 => 'font_size',
            3 => 'line_height',
          ),
        ),
        'surface-pack' => 
        array (
          'label' => 'Surface',
          'description' => 'Color treatment for the text block.',
          'icon' => 'palette',
          'fields' => 
          array (
            0 => 'color',
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'content' => 
      array (
        'type' => 'textarea',
        'label' => 'Content',
        'rows' => 6,
        'required' => true,
      ),
      'align' => 
      array (
        'type' => 'select',
        'label' => 'Alignment',
        'options' => 
        array (
          'left' => 'Left',
          'center' => 'Center',
          'right' => 'Right',
          'justify' => 'Justify',
        ),
      ),
      'color' => 
      array (
        'type' => 'color',
        'label' => 'Color',
      ),
      'font_size' => 
      array (
        'type' => 'select',
        'label' => 'Font size',
        'options' => 
        array (
          '0.875rem' => 'Small',
          '1rem' => 'Base',
          '1.125rem' => 'Large',
          '1.25rem' => 'Extra large',
          '1.5rem' => 'Display',
          '2rem' => 'Hero',
        ),
      ),
    ),
  ),
  'list' => 
  array (
    'name' => 'List',
    'category' => 'content',
    'icon' => 'list',
    'description' => 'Bulleted, numbered or unstyled list.',
    'default' => 
    array (
      'type' => 'list',
      'settings' => 
      array (
        'type' => 'disc',
        'items' => 
        array (
          0 => 
          array (
            'content' => 'First item',
          ),
          1 => 
          array (
            'content' => 'Second item',
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'type' => 
      array (
        'type' => 'select',
        'label' => 'Type',
        'options' => 
        array (
          'disc' => 'Bulleted',
          'decimal' => 'Numbered',
          'none' => 'Unstyled',
          'youtube' => 'YouTube',
          'vimeo' => 'Vimeo',
          'html5' => 'HTML5',
          'info' => 'Info',
          'success' => 'Success',
          'warning' => 'Warning',
          'error' => 'Error',
          'text' => 'Text',
          'email' => 'Email',
          'textarea' => 'Textarea',
          'select' => 'Select',
          'radio' => 'Radio',
          'checkbox' => 'Checkbox',
          'checkbox_group' => 'Checkbox group',
          'number' => 'Number',
          'date' => 'Date',
          'time' => 'Time',
          'hidden' => 'Hidden field',
          'calculator' => 'Calculator',
          'file' => 'File upload',
          'divider' => 'Divider',
          'html' => 'HTML content',
          'total' => 'Total',
          'subtotal' => 'Subtotal',
          'tax' => 'Tax',
          'discount' => 'Discount',
        ),
      ),
      'items' => 
      array (
        'type' => 'repeater',
        'label' => 'Items',
        'fields' => 
        array (
          0 => 
          array (
            'type' => 'text',
            'key' => 'content',
            'label' => 'Content',
          ),
        ),
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'content-pack' => 
        array (
          'label' => 'List content',
          'description' => 'List items and readable copy.',
          'icon' => 'list',
          'fields' => 
          array (
            0 => 'items',
          ),
        ),
        'layout-pack' => 
        array (
          'label' => 'List treatment',
          'description' => 'Marker style and list presentation.',
          'icon' => 'layers',
          'fields' => 
          array (
            0 => 'type',
          ),
        ),
      ),
    ),
  ),
  'faq' => 
  array (
    'name' => 'FAQ',
    'category' => 'content',
    'icon' => 'help-circle',
    'description' => 'Frequently asked questions with expandable answers.',
    'default' => 
    array (
      'type' => 'faq',
      'settings' => 
      array (
        'items' => 
        array (
          0 => 
          array (
            'question' => 'Question one',
            'answer' => 'Answer one',
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'items' => 
      array (
        'type' => 'repeater',
        'label' => 'Items',
        'fields' => 
        array (
          0 => 
          array (
            'type' => 'text',
            'key' => 'question',
            'label' => 'Question',
          ),
          1 => 
          array (
            'type' => 'textarea',
            'key' => 'answer',
            'label' => 'Answer',
          ),
        ),
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'content-pack' => 
        array (
          'label' => 'FAQ content',
          'description' => 'Question and answer pairs.',
          'icon' => 'help-circle',
          'fields' => 
          array (
            0 => 'items',
          ),
        ),
      ),
    ),
  ),
  'button' => 
  array (
    'name' => 'Button',
    'category' => 'content',
    'icon' => 'link',
    'description' => 'Call to action button with link settings.',
    'default' => 
    array (
      'type' => 'button',
      'settings' => 
      array (
        'text' => 'Button',
        'url' => '#',
        'target' => '_self',
        'style' => 'primary',
        'size' => 'md',
        'icon' => NULL,
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'button-treatment-pack' => 
        array (
          'label' => 'Button treatment',
          'description' => 'CTA label, destination and visual treatment.',
          'icon' => 'sparkles',
          'fields' => 
          array (
            0 => 'text',
            1 => 'url',
            2 => 'target',
            3 => 'style',
            4 => 'size',
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'text' => 
      array (
        'type' => 'text',
        'label' => 'Text',
        'required' => true,
      ),
      'url' => 
      array (
        'type' => 'text',
        'label' => 'URL',
        'required' => true,
      ),
      'target' => 
      array (
        'type' => 'select',
        'label' => 'Target',
        'options' => 
        array (
          '_self' => 'Same tab',
          '_blank' => 'New tab',
        ),
      ),
      'style' => 
      array (
        'type' => 'select',
        'label' => 'Style',
        'options' => 
        array (
          'primary' => 'Primary',
          'secondary' => 'Secondary',
          'outline' => 'Outline',
          'ghost' => 'Ghost',
          'solid' => 'Solid',
          'dashed' => 'Dashed',
          'dotted' => 'Dotted',
          'double' => 'Double',
          'line' => 'Line',
          'boxed' => 'Boxed',
          'pill' => 'Pill',
        ),
      ),
      'size' => 
      array (
        'type' => 'select',
        'label' => 'Size',
        'options' => 
        array (
          'sm' => 'Small',
          'md' => 'Medium',
          'lg' => 'Large',
          'xl' => 'Extra large',
        ),
      ),
    ),
  ),
  'image' => 
  array (
    'name' => 'Image',
    'category' => 'media',
    'icon' => 'image',
    'description' => 'Responsive image with media, alt text and display controls.',
    'default' => 
    array (
      'type' => 'image',
      'settings' => 
      array (
        'media_id' => NULL,
        'url' => '',
        'alt' => '',
        'width' => '100%',
        'height' => 'auto',
        'radius' => 'none',
        'shadow' => 'none',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'media-settings-pack' => 
        array (
          'label' => 'Media settings',
          'description' => 'Asset selection, fallback source and accessibility.',
          'icon' => 'image',
          'fields' => 
          array (
            0 => 'media_id',
            1 => 'url',
            2 => 'alt',
          ),
        ),
        'surface-pack' => 
        array (
          'label' => 'Surface',
          'description' => 'Corners and visual treatment around the image.',
          'icon' => 'palette',
          'fields' => 
          array (
            0 => 'radius',
            1 => 'shadow',
          ),
        ),
        'spacing-pack' => 
        array (
          'label' => 'Spacing',
          'description' => 'Frame dimensions for the image block.',
          'icon' => 'spacing',
          'fields' => 
          array (
            0 => 'width',
            1 => 'height',
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'media_id' => 
      array (
        'type' => 'number',
        'label' => 'Media item',
      ),
      'url' => 
      array (
        'type' => 'text',
        'label' => 'URL',
      ),
      'alt' => 
      array (
        'type' => 'text',
        'label' => 'Alt text',
      ),
      'width' => 
      array (
        'type' => 'text',
        'label' => 'Width',
      ),
      'height' => 
      array (
        'type' => 'text',
        'label' => 'Height',
      ),
      'radius' => 
      array (
        'type' => 'select',
        'label' => 'Radius',
        'options' => 
        array (
          'none' => 'None',
          'sm' => 'Small',
          'md' => 'Medium',
          'lg' => 'Large',
          'full' => 'Full',
        ),
      ),
      'shadow' => 
      array (
        'type' => 'select',
        'label' => 'Shadow',
        'options' => 
        array (
          'none' => 'None',
          'sm' => 'Small',
          'md' => 'Medium',
          'lg' => 'Large',
        ),
      ),
    ),
  ),
  'video' => 
  array (
    'name' => 'Video',
    'category' => 'media',
    'icon' => 'video',
    'description' => 'Embedded video from YouTube, Vimeo or direct sources.',
    'default' => 
    array (
      'type' => 'video',
      'settings' => 
      array (
        'type' => 'youtube',
        'url' => '',
        'autoplay' => false,
        'loop' => false,
        'muted' => false,
        'controls' => true,
        'width' => '100%',
        'ratio' => '16:9',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'media-settings-pack' => 
        array (
          'label' => 'Media settings',
          'description' => 'Source type, URL and player framing.',
          'icon' => 'image',
          'fields' => 
          array (
            0 => 'type',
            1 => 'url',
            2 => 'ratio',
            3 => 'width',
          ),
        ),
        'behavior-pack' => 
        array (
          'label' => 'Behavior',
          'description' => 'Playback toggles and embed behavior.',
          'icon' => 'toggle',
          'fields' => 
          array (
            0 => 'autoplay',
            1 => 'loop',
            2 => 'muted',
            3 => 'controls',
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'type' => 
      array (
        'type' => 'select',
        'label' => 'Type',
        'options' => 
        array (
          'disc' => 'Bulleted',
          'decimal' => 'Numbered',
          'none' => 'Unstyled',
          'youtube' => 'YouTube',
          'vimeo' => 'Vimeo',
          'html5' => 'HTML5',
          'info' => 'Info',
          'success' => 'Success',
          'warning' => 'Warning',
          'error' => 'Error',
          'text' => 'Text',
          'email' => 'Email',
          'textarea' => 'Textarea',
          'select' => 'Select',
          'radio' => 'Radio',
          'checkbox' => 'Checkbox',
          'checkbox_group' => 'Checkbox group',
          'number' => 'Number',
          'date' => 'Date',
          'time' => 'Time',
          'hidden' => 'Hidden field',
          'calculator' => 'Calculator',
          'file' => 'File upload',
          'divider' => 'Divider',
          'html' => 'HTML content',
          'total' => 'Total',
          'subtotal' => 'Subtotal',
          'tax' => 'Tax',
          'discount' => 'Discount',
        ),
      ),
      'url' => 
      array (
        'type' => 'text',
        'label' => 'URL',
      ),
      'autoplay' => 
      array (
        'type' => 'toggle',
        'label' => 'Autoplay',
      ),
      'loop' => 
      array (
        'type' => 'toggle',
        'label' => 'Loop',
      ),
      'muted' => 
      array (
        'type' => 'toggle',
        'label' => 'Muted',
      ),
      'controls' => 
      array (
        'type' => 'toggle',
        'label' => 'Show controls',
      ),
      'ratio' => 
      array (
        'type' => 'select',
        'label' => 'Aspect ratio',
        'options' => 
        array (
          '16:9' => '16:9',
          '4:3' => '4:3',
          '1:1' => '1:1',
          '21:9' => 'Cinematic',
        ),
      ),
    ),
  ),
  'gallery' => 
  array (
    'name' => 'Gallery',
    'category' => 'media',
    'icon' => 'images',
    'description' => 'Image gallery with grid, slider and lightbox options.',
    'default' => 
    array (
      'type' => 'gallery',
      'settings' => 
      array (
        'images' => 
        array (
        ),
        'layout' => 'grid',
        'columns' => 3,
        'tablet_columns' => 2,
        'mobile_columns' => 1,
        'gap' => 'md',
        'radius' => 'md',
        'aspect_ratio' => '4:3',
        'object_fit' => 'cover',
        'caption_mode' => 'overlay',
        'lightbox' => true,
        'lightbox_effect' => 'zoom',
        'show_arrows' => true,
        'show_dots' => true,
        'autoplay' => false,
        'interval' => 5000,
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'media-settings-pack' => 
        array (
          'label' => 'Media settings',
          'description' => 'Gallery images, captions and media order.',
          'icon' => 'image',
          'fields' => 
          array (
            0 => 'images',
          ),
        ),
        'layout-pack' => 
        array (
          'label' => 'Layout',
          'description' => 'Grid, masonry or slideshow composition.',
          'icon' => 'layers',
          'fields' => 
          array (
            0 => 'layout',
            1 => 'columns',
            2 => 'tablet_columns',
            3 => 'mobile_columns',
            4 => 'gap',
          ),
        ),
        'surface-pack' => 
        array (
          'label' => 'Surface',
          'description' => 'Image frame, ratio, captions and corners.',
          'icon' => 'palette',
          'fields' => 
          array (
            0 => 'aspect_ratio',
            1 => 'object_fit',
            2 => 'caption_mode',
            3 => 'radius',
          ),
        ),
        'behavior-pack' => 
        array (
          'label' => 'Behavior',
          'description' => 'Lightbox and slideshow controls.',
          'icon' => 'toggle',
          'fields' => 
          array (
            0 => 'lightbox',
            1 => 'lightbox_effect',
            2 => 'show_arrows',
            3 => 'show_dots',
            4 => 'autoplay',
            5 => 'interval',
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'images' => 
      array (
        'type' => 'repeater',
        'label' => 'Images',
        'fields' => 
        array (
          0 => 
          array (
            'type' => 'media',
            'key' => 'media_id',
            'label' => 'Media item',
          ),
          1 => 
          array (
            'type' => 'text',
            'key' => 'url',
            'label' => 'URL',
          ),
          2 => 
          array (
            'type' => 'text',
            'key' => 'alt',
            'label' => 'Alt text',
          ),
          3 => 
          array (
            'type' => 'text',
            'key' => 'caption',
            'label' => 'Caption',
          ),
          4 => 
          array (
            'type' => 'text',
            'key' => 'link',
            'label' => 'Link',
          ),
        ),
      ),
      'layout' => 
      array (
        'type' => 'select',
        'label' => 'Layout',
        'options' => 
        array (
          'grid' => 'Grid',
          'list' => 'List',
          'masonry' => 'Masonry',
          'slider' => 'Slider',
          'carousel' => 'Carousel',
          'cards' => 'Cards',
          'minimal' => 'Minimal',
          'vertical' => 'Vertical',
          'horizontal' => 'Horizontal',
          'inline' => 'Inline',
        ),
      ),
      'columns' => 
      array (
        'type' => 'select',
        'label' => 'Columns',
        'options' => 
        array (
          1 => '1',
          2 => '2',
          3 => '3',
          4 => '4',
          5 => '5',
          6 => '6',
        ),
      ),
      'tablet_columns' => 
      array (
        'type' => 'select',
        'label' => 'Tablet columns',
        'options' => 
        array (
          1 => '1',
          2 => '2',
          3 => '3',
          4 => '4',
        ),
      ),
      'mobile_columns' => 
      array (
        'type' => 'select',
        'label' => 'Mobile columns',
        'options' => 
        array (
          1 => '1',
          2 => '2',
        ),
      ),
      'gap' => 
      array (
        'type' => 'select',
        'label' => 'Gap',
        'options' => 
        array (
          'none' => 'None',
          'sm' => 'Small',
          'md' => 'Medium',
          'lg' => 'Large',
        ),
      ),
      'radius' => 
      array (
        'type' => 'select',
        'label' => 'Radius',
        'options' => 
        array (
          'none' => 'None',
          'sm' => 'Small',
          'md' => 'Medium',
          'lg' => 'Large',
          'full' => 'Full',
        ),
      ),
      'aspect_ratio' => 
      array (
        'type' => 'select',
        'label' => 'Aspect ratio',
        'options' => 
        array (
          'auto' => 'Original',
          '1:1' => '1:1',
          '4:3' => '4:3',
          '3:2' => '3:2',
          '16:9' => '16:9',
          '21:9' => '21:9',
        ),
      ),
      'object_fit' => 
      array (
        'type' => 'select',
        'label' => 'Object fit',
        'options' => 
        array (
          'cover' => 'Cover',
          'contain' => 'Contain',
        ),
      ),
      'caption_mode' => 
      array (
        'type' => 'select',
        'label' => 'Captions',
        'options' => 
        array (
          'none' => 'Hidden',
          'overlay' => 'Overlay',
          'below' => 'Below image',
        ),
      ),
      'lightbox' => 
      array (
        'type' => 'toggle',
        'label' => 'Enable lightbox',
      ),
      'lightbox_effect' => 
      array (
        'type' => 'select',
        'label' => 'Lightbox effect',
        'options' => 
        array (
          'fade' => 'Fade',
          'zoom' => 'Zoom',
        ),
      ),
      'show_arrows' => 
      array (
        'type' => 'toggle',
        'label' => 'Show arrows',
      ),
      'show_dots' => 
      array (
        'type' => 'toggle',
        'label' => 'Show dots',
      ),
      'autoplay' => 
      array (
        'type' => 'toggle',
        'label' => 'Autoplay',
      ),
      'interval' => 
      array (
        'type' => 'number',
        'label' => 'Interval',
      ),
    ),
  ),
  'icon' => 
  array (
    'name' => 'Icon',
    'category' => 'content',
    'icon' => 'star',
    'description' => 'Single icon with size, color and surface controls.',
    'default' => 
    array (
      'type' => 'icon',
      'settings' => 
      array (
        'icon' => 'star',
        'size' => 'md',
        'color' => '#3b82f6',
        'background' => NULL,
        'radius' => 'md',
      ),
    ),
    'fields' => 
    array (
      'icon' => 
      array (
        'type' => 'select',
        'label' => 'Icon',
        'options' => 
        array (
          'star' => 'Star',
          'heart' => 'Heart',
          'check' => 'Check',
          'x' => 'X',
          'arrow' => 'Arrow',
          'dots' => 'Dots',
        ),
      ),
      'size' => 
      array (
        'type' => 'select',
        'label' => 'Size',
        'options' => 
        array (
          'sm' => 'Small',
          'md' => 'Medium',
          'lg' => 'Large',
          'xl' => 'Extra large',
        ),
      ),
      'color' => 
      array (
        'type' => 'color',
        'label' => 'Color',
      ),
      'background' => 
      array (
        'type' => 'color',
        'label' => 'Background',
      ),
      'radius' => 
      array (
        'type' => 'select',
        'label' => 'Radius',
        'options' => 
        array (
          'none' => 'None',
          'sm' => 'Small',
          'md' => 'Medium',
          'lg' => 'Large',
          'full' => 'Full',
        ),
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'icon-pack' => 
        array (
          'label' => 'Icon',
          'description' => 'Glyph, size and visual treatment.',
          'icon' => 'star',
          'fields' => 
          array (
            0 => 'icon',
            1 => 'size',
            2 => 'color',
            3 => 'background',
            4 => 'radius',
          ),
        ),
      ),
    ),
  ),
  'hero' => 
  array (
    'name' => 'Hero',
    'category' => 'layout',
    'icon' => 'layers',
    'description' => 'Large hero section with headline, supporting text, media and CTA.',
    'default' => 
    array (
      'type' => 'hero',
      'settings' => 
      array (
        'title' => 'Hero title',
        'subtitle' => 'Add a concise supporting message.',
        'background' => '',
        'title_color' => '#ffffff',
        'subtitle_color' => '#ffffff',
        'button_text' => 'Get started',
        'button_url' => '#',
        'button_target' => '_self',
        'button_bg_color' => '#3b82f6',
        'button_text_color' => '#ffffff',
        'button_border_color' => 'transparent',
        'padding_top' => 80,
        'padding_bottom' => 80,
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'typography-pack' => 
        array (
          'label' => 'Typography',
          'description' => 'Headline hierarchy and hero copy.',
          'icon' => 'text',
          'fields' => 
          array (
            0 => 'title',
            1 => 'subtitle',
            2 => 'title_color',
            3 => 'subtitle_color',
          ),
        ),
        'media-settings-pack' => 
        array (
          'label' => 'Media settings',
          'description' => 'Background asset and hero media surface.',
          'icon' => 'image',
          'fields' => 
          array (
            0 => 'background',
          ),
        ),
        'button-treatment-pack' => 
        array (
          'label' => 'Button treatment',
          'description' => 'Hero CTA label, destination and button colors.',
          'icon' => 'sparkles',
          'fields' => 
          array (
            0 => 'button_text',
            1 => 'button_url',
            2 => 'button_target',
            3 => 'button_bg_color',
            4 => 'button_text_color',
            5 => 'button_border_color',
          ),
        ),
        'spacing-pack' => 
        array (
          'label' => 'Spacing',
          'description' => 'Vertical breathing room inside the hero.',
          'icon' => 'spacing',
          'fields' => 
          array (
            0 => 'padding_top',
            1 => 'padding_bottom',
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'title' => 
      array (
        'type' => 'text',
        'label' => 'Title',
        'required' => true,
      ),
      'subtitle' => 
      array (
        'type' => 'textarea',
        'label' => 'Subtitle',
        'rows' => 3,
      ),
      'background' => 
      array (
        'type' => 'media',
        'label' => 'Background',
      ),
      'title_color' => 
      array (
        'type' => 'color',
        'label' => 'Title Color',
      ),
      'subtitle_color' => 
      array (
        'type' => 'color',
        'label' => 'Subtitle Color',
      ),
      'button_text' => 
      array (
        'type' => 'text',
        'label' => 'Button text',
      ),
      'button_url' => 
      array (
        'type' => 'text',
        'label' => 'Button URL',
      ),
      'button_target' => 
      array (
        'type' => 'select',
        'label' => 'Button target',
        'options' => 
        array (
          '_self' => ' Self',
          '_blank' => ' Blank',
        ),
      ),
      'button_bg_color' => 
      array (
        'type' => 'color',
        'label' => 'Button background',
      ),
      'button_text_color' => 
      array (
        'type' => 'color',
        'label' => 'Button text color',
      ),
      'button_border_color' => 
      array (
        'type' => 'color',
        'label' => 'Button border color',
      ),
      'padding_top' => 
      array (
        'type' => 'number',
        'label' => 'Top padding',
        'min' => 0,
        'max' => 200,
      ),
      'padding_bottom' => 
      array (
        'type' => 'number',
        'label' => 'Bottom padding',
        'min' => 0,
        'max' => 200,
      ),
    ),
  ),
  'columns' => 
  array (
    'name' => 'Columns',
    'category' => 'layout',
    'icon' => 'columns',
    'description' => 'Multi-column layout container.',
    'default' => 
    array (
      'type' => 'columns',
      'settings' => 
      array (
        'count' => 2,
        'gap' => 'md',
        'columns' => 
        array (
          0 => 
          array (
            'blocks' => 
            array (
            ),
            'width' => 6,
          ),
          1 => 
          array (
            'blocks' => 
            array (
            ),
            'width' => 6,
          ),
        ),
      ),
    ),
    'fields' => 
    array (
      'count' => 
      array (
        'type' => 'select',
        'label' => 'Count',
        'options' => 
        array (
          1 => '1',
          2 => '2',
          3 => '3',
          4 => '4',
        ),
      ),
      'gap' => 
      array (
        'type' => 'select',
        'label' => 'Gap',
        'options' => 
        array (
          'none' => 'None',
          'sm' => 'Small',
          'md' => 'Medium',
          'lg' => 'Large',
        ),
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'layout-pack' => 
        array (
          'label' => 'Column layout',
          'description' => 'Column count and spacing.',
          'icon' => 'columns',
          'fields' => 
          array (
            0 => 'count',
            1 => 'gap',
          ),
        ),
      ),
    ),
  ),
  'container' => 
  array (
    'name' => 'Container',
    'category' => 'layout',
    'icon' => 'box',
    'description' => 'Content wrapper with max width and padding controls.',
    'default' => 
    array (
      'type' => 'container',
      'settings' => 
      array (
        'max_width' => '7xl',
        'padding' => 
        array (
          'top' => 16,
          'bottom' => 16,
          'left' => 4,
          'right' => 4,
        ),
        'blocks' => 
        array (
        ),
      ),
    ),
    'fields' => 
    array (
      'max_width' => 
      array (
        'type' => 'select',
        'label' => 'Max width',
        'options' => 
        array (
          'sm' => '640px',
          'md' => '768px',
          'lg' => '1024px',
          'xl' => '1280px',
          '2xl' => '1536px',
          '3xl' => '1792px',
          '4xl' => '2048px',
          '5xl' => '2560px',
          '6xl' => '2880px',
          '7xl' => '3200px',
        ),
      ),
      'padding_top' => 
      array (
        'type' => 'number',
        'label' => 'Top padding',
      ),
      'padding_bottom' => 
      array (
        'type' => 'number',
        'label' => 'Bottom padding',
      ),
      'padding_left' => 
      array (
        'type' => 'number',
        'label' => 'Left padding',
      ),
      'padding_right' => 
      array (
        'type' => 'number',
        'label' => 'Right padding',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'layout-pack' => 
        array (
          'label' => 'Container layout',
          'description' => 'Content width and inner spacing.',
          'icon' => 'box',
          'fields' => 
          array (
            0 => 'max_width',
            1 => 'padding_top',
            2 => 'padding_bottom',
            3 => 'padding_left',
            4 => 'padding_right',
          ),
        ),
      ),
    ),
  ),
  'spacer' => 
  array (
    'name' => 'Spacer',
    'category' => 'layout',
    'icon' => 'arrows-expand',
    'description' => 'Empty vertical spacing block.',
    'default' => 
    array (
      'type' => 'spacer',
      'settings' => 
      array (
        'height' => 32,
      ),
    ),
    'fields' => 
    array (
      'height' => 
      array (
        'type' => 'number',
        'label' => 'Height',
        'min' => 0,
        'max' => 500,
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'spacing-pack' => 
        array (
          'label' => 'Spacing',
          'description' => 'Vertical empty space.',
          'icon' => 'spacing',
          'fields' => 
          array (
            0 => 'height',
          ),
        ),
      ),
    ),
  ),
  'divider' => 
  array (
    'name' => 'Divider',
    'category' => 'layout',
    'icon' => 'minus',
    'description' => 'Horizontal separator line.',
    'default' => 
    array (
      'type' => 'divider',
      'settings' => 
      array (
        'style' => 'solid',
        'color' => '#e5e7eb',
        'thickness' => 1,
        'width' => '100%',
      ),
    ),
    'fields' => 
    array (
      'style' => 
      array (
        'type' => 'select',
        'label' => 'Style',
        'options' => 
        array (
          'primary' => 'Primary',
          'secondary' => 'Secondary',
          'outline' => 'Outline',
          'ghost' => 'Ghost',
          'solid' => 'Solid',
          'dashed' => 'Dashed',
          'dotted' => 'Dotted',
          'double' => 'Double',
          'line' => 'Line',
          'boxed' => 'Boxed',
          'pill' => 'Pill',
        ),
      ),
      'color' => 
      array (
        'type' => 'color',
        'label' => 'Color',
      ),
      'thickness' => 
      array (
        'type' => 'number',
        'label' => 'Thickness',
        'min' => 1,
        'max' => 10,
      ),
      'width' => 
      array (
        'type' => 'text',
        'label' => 'Width',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'surface-pack' => 
        array (
          'label' => 'Divider style',
          'description' => 'Line treatment, color and dimensions.',
          'icon' => 'minus',
          'fields' => 
          array (
            0 => 'style',
            1 => 'color',
            2 => 'thickness',
            3 => 'width',
          ),
        ),
      ),
    ),
  ),
  'news-feed' => 
  array (
    'name' => 'News Feed',
    'category' => 'dynamic',
    'icon' => 'newspaper',
    'description' => 'Dynamic feed of recent posts or entries.',
    'default' => 
    array (
      'type' => 'news-feed',
      'settings' => 
      array (
        'count' => 3,
        'category' => NULL,
        'show_image' => true,
        'show_excerpt' => true,
        'show_date' => true,
        'columns' => 3,
        'layout' => 'grid',
      ),
    ),
    'fields' => 
    array (
      'count' => 
      array (
        'type' => 'number',
        'label' => 'Count',
        'min' => 1,
        'max' => 50,
      ),
      'category' => 
      array (
        'type' => 'select',
        'label' => 'Category',
        'options' => 
        array (
          'all' => 'All categories',
          'news' => 'News',
          'blog' => 'Blog',
          'updates' => 'Updates',
        ),
      ),
      'show_image' => 
      array (
        'type' => 'toggle',
        'label' => 'Show image',
      ),
      'show_excerpt' => 
      array (
        'type' => 'toggle',
        'label' => 'Show excerpt',
      ),
      'show_date' => 
      array (
        'type' => 'toggle',
        'label' => 'Show date',
      ),
      'columns' => 
      array (
        'type' => 'select',
        'label' => 'Columns',
        'options' => 
        array (
          1 => '1',
          2 => '2',
          3 => '3',
          4 => '4',
          5 => '5',
          6 => '6',
        ),
      ),
      'layout' => 
      array (
        'type' => 'select',
        'label' => 'Layout',
        'options' => 
        array (
          'grid' => 'Grid',
          'list' => 'List',
          'masonry' => 'Masonry',
          'slider' => 'Slider',
          'carousel' => 'Carousel',
          'cards' => 'Cards',
          'minimal' => 'Minimal',
          'vertical' => 'Vertical',
          'horizontal' => 'Horizontal',
          'inline' => 'Inline',
        ),
      ),
    ),
  ),
  'testimonials' => 
  array (
    'name' => 'Testimonials',
    'category' => 'dynamic',
    'icon' => 'chat',
    'description' => 'Customer testimonials and reviews layout.',
    'default' => 
    array (
      'type' => 'testimonials',
      'settings' => 
      array (
        'layout' => 'grid',
        'autoplay' => false,
        'show_rating' => true,
      ),
    ),
    'fields' => 
    array (
      'layout' => 
      array (
        'type' => 'select',
        'label' => 'Layout',
        'options' => 
        array (
          'grid' => 'Grid',
          'list' => 'List',
          'masonry' => 'Masonry',
          'slider' => 'Slider',
          'carousel' => 'Carousel',
          'cards' => 'Cards',
          'minimal' => 'Minimal',
          'vertical' => 'Vertical',
          'horizontal' => 'Horizontal',
          'inline' => 'Inline',
        ),
      ),
      'autoplay' => 
      array (
        'type' => 'toggle',
        'label' => 'Autoplay',
      ),
      'show_rating' => 
      array (
        'type' => 'toggle',
        'label' => 'Show rating',
      ),
    ),
  ),
  'counter' => 
  array (
    'name' => 'Counter',
    'category' => 'dynamic',
    'icon' => 'chart-bar',
    'description' => 'Animated numeric counter with label and prefix/suffix.',
    'default' => 
    array (
      'type' => 'counter',
      'settings' => 
      array (
        'value' => 100,
        'prefix' => '',
        'suffix' => '+',
        'duration' => 2000,
        'label' => 'Projects completed',
      ),
    ),
    'fields' => 
    array (
      'value' => 
      array (
        'type' => 'number',
        'label' => 'Value',
        'required' => true,
      ),
      'prefix' => 
      array (
        'type' => 'text',
        'label' => 'Prefix',
      ),
      'suffix' => 
      array (
        'type' => 'text',
        'label' => 'Suffix',
      ),
      'duration' => 
      array (
        'type' => 'number',
        'label' => 'Duration',
        'min' => 100,
        'max' => 5000,
      ),
      'label' => 
      array (
        'type' => 'text',
        'label' => 'Label',
      ),
    ),
  ),
  'pricing-table' => 
  array (
    'name' => 'Pricing Table',
    'category' => 'dynamic',
    'icon' => 'credit-card',
    'description' => 'Pricing cards for plans or offers.',
    'default' => 
    array (
      'type' => 'pricing-table',
      'settings' => 
      array (
        'columns' => 3,
      ),
    ),
    'fields' => 
    array (
      'columns' => 
      array (
        'type' => 'select',
        'label' => 'Columns',
        'options' => 
        array (
          1 => '1',
          2 => '2',
          3 => '3',
          4 => '4',
          5 => '5',
          6 => '6',
        ),
      ),
    ),
  ),
  'form' => 
  array (
    'name' => 'Form',
    'category' => 'dynamic',
    'icon' => 'form',
    'description' => 'Embedded or inline Vertex form block.',
    'default' => 
    array (
      'type' => 'form',
      'settings' => 
      array (
        'mode' => 'existing',
        'form_id' => NULL,
        'title' => 'Contact form',
        'description' => 'Leave a request and we will get back to you.',
        'fields' => 
        array (
          0 => 
          array (
            'type' => 'text',
            'name' => 'name',
            'label' => 'Your name',
            'required' => true,
            'placeholder' => 'Jane Doe',
          ),
          1 => 
          array (
            'type' => 'email',
            'name' => 'email',
            'label' => 'Email',
            'required' => true,
            'placeholder' => 'jane@example.com',
          ),
        ),
        'button_text' => 'Submit',
        'submit_label' => 'Submit',
        'success_message' => 'Thank you. We will contact you soon.',
        'error_message' => 'Something went wrong. Please try again.',
        'action_url' => '',
        'method' => 'POST',
        'multipage' => false,
        'show_progress' => true,
        'show_page_titles' => false,
        'ajax' => true,
        'redirect_url' => '',
        'refresh_on_success' => false,
        'enable_honeypot' => true,
        'enable_recaptcha' => false,
        'recaptcha_version' => 'v2',
        'notify_admin' => true,
        'admin_emails' => '',
        'notify_user' => false,
        'user_email_field' => 'email',
        'user_email_subject' => 'Thank you for your request',
        'user_email_template' => 'form_confirmation',
        'tax_enabled' => false,
        'tax_rate' => 0,
        'currency' => '$',
        'currency_position' => 'before',
        'thousand_separator' => ',',
        'decimal_separator' => '.',
        'theme' => 'default',
        'custom_css' => '',
        'layout' => 'vertical',
        'label_position' => 'top',
        'show_labels' => true,
        'show_placeholders' => true,
        'required_mark' => true,
      ),
    ),
    'fields' => 
    array (
      'mode' => 
      array (
        'type' => 'select',
        'label' => 'Mode',
        'options' => 
        array (
          'existing' => 'Select existing form',
          'inline' => 'Build inline form',
        ),
      ),
      'form_id' => 
      array (
        'type' => 'select',
        'label' => 'Existing form',
        'options' => '\\Vertex\\Forms\\Models\\Form::all()->pluck("name","id")->toArray()',
        'depends_on' => 
        array (
          'mode' => 'existing',
        ),
      ),
      'title' => 
      array (
        'type' => 'text',
        'label' => 'Title',
      ),
      'description' => 
      array (
        'type' => 'textarea',
        'label' => 'Description',
        'rows' => 3,
      ),
      'submit_label' => 
      array (
        'type' => 'text',
        'label' => 'Submit label',
        'default' => 'Submit',
      ),
      'success_message' => 
      array (
        'type' => 'textarea',
        'label' => 'Success message',
        'rows' => 2,
      ),
      'error_message' => 
      array (
        'type' => 'textarea',
        'label' => 'Error message',
        'rows' => 2,
      ),
      'fields' => 
      array (
        'type' => 'repeater',
        'label' => 'Fields',
        'depends_on' => 
        array (
          'mode' => 'inline',
        ),
        'fields' => 
        array (
          0 => 
          array (
            'type' => 'text',
            'key' => 'name',
            'label' => 'Name',
            'required' => true,
            'placeholder' => 'field_name',
          ),
          1 => 
          array (
            'type' => 'text',
            'key' => 'label',
            'label' => 'Label',
            'required' => true,
          ),
          2 => 
          array (
            'type' => 'select',
            'key' => 'type',
            'label' => 'Type',
            'options' => 
            array (
              'disc' => 'Bulleted',
              'decimal' => 'Numbered',
              'none' => 'Unstyled',
              'youtube' => 'YouTube',
              'vimeo' => 'Vimeo',
              'html5' => 'HTML5',
              'info' => 'Info',
              'success' => 'Success',
              'warning' => 'Warning',
              'error' => 'Error',
              'text' => 'Text',
              'email' => 'Email',
              'textarea' => 'Textarea',
              'select' => 'Select',
              'radio' => 'Radio',
              'checkbox' => 'Checkbox',
              'checkbox_group' => 'Checkbox group',
              'number' => 'Number',
              'date' => 'Date',
              'time' => 'Time',
              'hidden' => 'Hidden field',
              'calculator' => 'Calculator',
              'file' => 'File upload',
              'divider' => 'Divider',
              'html' => 'HTML content',
              'total' => 'Total',
              'subtotal' => 'Subtotal',
              'tax' => 'Tax',
              'discount' => 'Discount',
            ),
          ),
          3 => 
          array (
            'type' => 'toggle',
            'key' => 'required',
            'label' => 'Required',
          ),
          4 => 
          array (
            'type' => 'text',
            'key' => 'placeholder',
            'label' => 'Placeholder',
          ),
          5 => 
          array (
            'type' => 'text',
            'key' => 'default_value',
            'label' => 'Default Value',
          ),
          6 => 
          array (
            'type' => 'help',
            'key' => 'help_text',
            'label' => 'Help Text',
          ),
          7 => 
          array (
            'type' => 'select',
            'key' => 'width',
            'label' => 'Width',
            'options' => 
            array (
              'full' => 'Full',
              'half' => 'Half',
              'third' => 'Third',
            ),
          ),
        ),
      ),
      'calculator_options' => 
      array (
        'type' => 'repeater',
        'label' => 'Calculator options',
        'depends_on' => 
        array (
          'mode' => 'inline',
        ),
        'fields' => 
        array (
          0 => 
          array (
            'type' => 'text',
            'key' => 'name',
            'label' => 'Name',
          ),
          1 => 
          array (
            'type' => 'select',
            'key' => 'type',
            'label' => 'Type',
            'options' => 
            array (
              'disc' => 'Bulleted',
              'decimal' => 'Numbered',
              'none' => 'Unstyled',
              'youtube' => 'YouTube',
              'vimeo' => 'Vimeo',
              'html5' => 'HTML5',
              'info' => 'Info',
              'success' => 'Success',
              'warning' => 'Warning',
              'error' => 'Error',
              'text' => 'Text',
              'email' => 'Email',
              'textarea' => 'Textarea',
              'select' => 'Select',
              'radio' => 'Radio',
              'checkbox' => 'Checkbox',
              'checkbox_group' => 'Checkbox group',
              'number' => 'Number',
              'date' => 'Date',
              'time' => 'Time',
              'hidden' => 'Hidden field',
              'calculator' => 'Calculator',
              'file' => 'File upload',
              'divider' => 'Divider',
              'html' => 'HTML content',
              'total' => 'Total',
              'subtotal' => 'Subtotal',
              'tax' => 'Tax',
              'discount' => 'Discount',
            ),
          ),
          2 => 
          array (
            'type' => 'textarea',
            'key' => 'formula',
            'label' => 'Formula',
            'rows' => 2,
          ),
          3 => 
          array (
            'type' => 'text',
            'key' => 'prefix',
            'label' => 'Prefix',
          ),
          4 => 
          array (
            'type' => 'text',
            'key' => 'suffix',
            'label' => 'Suffix',
          ),
          5 => 
          array (
            'type' => 'number',
            'key' => 'precision',
            'label' => 'Precision',
            'default' => 2,
          ),
          6 => 
          array (
            'type' => 'toggle',
            'key' => 'live',
            'label' => 'Calculate live',
          ),
        ),
      ),
      'enable_conditions' => 
      array (
        'type' => 'toggle',
        'label' => 'Enable conditions',
      ),
      'conditions' => 
      array (
        'type' => 'repeater',
        'label' => 'Conditions',
        'depends_on' => 
        array (
          'enable_conditions' => true,
        ),
        'fields' => 
        array (
          0 => 
          array (
            'type' => 'select',
            'key' => 'depends_on',
            'label' => 'Depends On',
            'options' => 'dynamic_fields',
          ),
          1 => 
          array (
            'type' => 'select',
            'key' => 'operator',
            'label' => 'Operator',
            'options' => 
            array (
              'equals' => 'Equals',
              'not_equals' => 'Does not equal',
              'contains' => 'Contains',
              'greater_than' => 'Greater than',
              'less_than' => 'Less than',
              'is_empty' => 'Is empty',
              'is_not_empty' => 'Is not empty',
            ),
          ),
          2 => 
          array (
            'type' => 'text',
            'key' => 'value',
            'label' => 'Value',
          ),
          3 => 
          array (
            'type' => 'select',
            'key' => 'action',
            'label' => 'Action',
            'options' => 
            array (
              'show' => 'Show field',
              'hide' => 'Hide field',
            ),
          ),
        ),
      ),
      'multipage' => 
      array (
        'type' => 'toggle',
        'label' => 'Multipage form',
      ),
      'page_titles' => 
      array (
        'type' => 'text',
        'label' => 'Page titles',
        'depends_on' => 
        array (
          'multipage' => true,
        ),
      ),
      'enable_honeypot' => 
      array (
        'type' => 'toggle',
        'label' => 'Enable honeypot',
        'default' => true,
      ),
      'enable_recaptcha' => 
      array (
        'type' => 'toggle',
        'label' => 'Enable reCAPTCHA',
      ),
      'recaptcha_version' => 
      array (
        'type' => 'select',
        'label' => 'reCAPTCHA version',
        'options' => 
        array (
          'v2' => 'v2 Checkbox',
          'v3' => 'v3 Score',
        ),
        'depends_on' => 
        array (
          'enable_recaptcha' => true,
        ),
      ),
      'entry_limit' => 
      array (
        'type' => 'number',
        'label' => 'Entry limit',
      ),
      'daily_limit' => 
      array (
        'type' => 'number',
        'label' => 'Daily limit',
      ),
      'available_from' => 
      array (
        'type' => 'datetime',
        'label' => 'Available from',
      ),
      'available_to' => 
      array (
        'type' => 'datetime',
        'label' => 'Available to',
      ),
      'notify_admin' => 
      array (
        'type' => 'toggle',
        'label' => 'Notify admin',
        'default' => true,
      ),
      'admin_emails' => 
      array (
        'type' => 'textarea',
        'label' => 'Admin emails',
        'depends_on' => 
        array (
          'notify_admin' => true,
        ),
      ),
      'notify_user' => 
      array (
        'type' => 'toggle',
        'label' => 'Notify user',
      ),
      'user_email_field' => 
      array (
        'type' => 'select',
        'label' => 'User email field',
        'depends_on' => 
        array (
          'notify_user' => true,
        ),
      ),
      'user_email_subject' => 
      array (
        'type' => 'text',
        'label' => 'User email subject',
        'depends_on' => 
        array (
          'notify_user' => true,
        ),
      ),
      'user_email_template' => 
      array (
        'type' => 'select',
        'label' => 'User email template',
        'depends_on' => 
        array (
          'notify_user' => true,
        ),
        'options' => 
        array (
          'form_confirmation' => 'Request confirmation',
        ),
      ),
      'tax_enabled' => 
      array (
        'type' => 'toggle',
        'label' => 'Enable tax',
      ),
      'tax_rate' => 
      array (
        'type' => 'number',
        'label' => 'Tax rate',
        'depends_on' => 
        array (
          'tax_enabled' => true,
        ),
        'step' => 0.1,
      ),
      'currency' => 
      array (
        'type' => 'text',
        'label' => 'Currency',
        'default' => '$',
      ),
      'currency_position' => 
      array (
        'type' => 'select',
        'label' => 'Currency position',
        'options' => 
        array (
          'before' => 'Before amount',
          'after' => 'After amount',
        ),
      ),
      'thousand_separator' => 
      array (
        'type' => 'text',
        'label' => 'Thousands separator',
        'default' => '',
      ),
      'decimal_separator' => 
      array (
        'type' => 'text',
        'label' => 'Decimal separator',
        'default' => '.',
      ),
      'theme' => 
      array (
        'type' => 'select',
        'label' => 'Theme',
        'options' => 
        array (
          'default' => 'Default',
          'minimal' => 'Minimal',
          'card' => 'Card',
        ),
      ),
      'layout' => 
      array (
        'type' => 'select',
        'label' => 'Layout',
        'options' => 
        array (
          'grid' => 'Grid',
          'list' => 'List',
          'masonry' => 'Masonry',
          'slider' => 'Slider',
          'carousel' => 'Carousel',
          'cards' => 'Cards',
          'minimal' => 'Minimal',
          'vertical' => 'Vertical',
          'horizontal' => 'Horizontal',
          'inline' => 'Inline',
        ),
      ),
      'label_position' => 
      array (
        'type' => 'select',
        'label' => 'Label position',
        'options' => 
        array (
          'top' => 'Top',
          'left' => 'Left',
          'inside' => 'Inside',
        ),
      ),
      'show_labels' => 
      array (
        'type' => 'toggle',
        'label' => 'Show labels',
        'default' => true,
      ),
      'required_mark' => 
      array (
        'type' => 'toggle',
        'label' => 'Show required mark',
        'default' => true,
      ),
      'custom_css' => 
      array (
        'type' => 'textarea',
        'label' => 'Custom CSS',
        'rows' => 3,
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'form-content-pack' => 
        array (
          'label' => 'Form content',
          'description' => 'Form source, fields and labels.',
          'icon' => 'form',
          'fields' => 
          array (
            0 => 'mode',
            1 => 'form_id',
            2 => 'title',
            3 => 'description',
            4 => 'fields',
            5 => 'submit_label',
          ),
        ),
        'form-behavior-pack' => 
        array (
          'label' => 'Form behavior',
          'description' => 'Submission, validation and notification settings.',
          'icon' => 'toggle',
          'fields' => 
          array (
            0 => 'success_message',
            1 => 'error_message',
            2 => 'multipage',
            3 => 'ajax',
            4 => 'enable_honeypot',
            5 => 'enable_recaptcha',
            6 => 'notify_admin',
            7 => 'notify_user',
          ),
        ),
        'form-style-pack' => 
        array (
          'label' => 'Form style',
          'description' => 'Theme, layout and label presentation.',
          'icon' => 'palette',
          'fields' => 
          array (
            0 => 'theme',
            1 => 'layout',
            2 => 'label_position',
            3 => 'show_labels',
            4 => 'required_mark',
            5 => 'custom_css',
          ),
        ),
      ),
    ),
  ),
  'seo-meta' => 
  array (
    'name' => 'SEO Metadata',
    'category' => 'seo',
    'icon' => 'search',
    'description' => 'Page-level search metadata block.',
    'default' => 
    array (
      'type' => 'seo-meta',
      'settings' => 
      array (
        'title' => '',
        'description' => '',
        'keywords' => 
        array (
        ),
        'robots' => 'index, follow',
        'canonical' => '',
      ),
    ),
    'fields' => 
    array (
      'title' => 
      array (
        'type' => 'text',
        'label' => 'Title',
      ),
      'description' => 
      array (
        'type' => 'textarea',
        'label' => 'Description',
        'rows' => 3,
      ),
      'keywords' => 
      array (
        'type' => 'text',
        'label' => 'Keywords',
      ),
      'robots' => 
      array (
        'type' => 'select',
        'label' => 'Robots',
        'options' => 
        array (
          'index, follow' => 'Index, follow',
          'noindex, follow' => 'No index, follow',
          'noindex, nofollow' => 'No index, no follow',
        ),
      ),
      'canonical' => 
      array (
        'type' => 'text',
        'label' => 'Canonical URL',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'seo-pack' => 
        array (
          'label' => 'SEO metadata',
          'description' => 'Search metadata and indexing hints.',
          'icon' => 'search',
          'fields' => 
          array (
            0 => 'title',
            1 => 'description',
            2 => 'keywords',
            3 => 'robots',
            4 => 'canonical',
          ),
        ),
      ),
    ),
  ),
  'accordion' => 
  array (
    'name' => 'Accordion',
    'category' => 'interactive',
    'icon' => 'chevron-down',
    'description' => 'Expandable content panels.',
    'default' => 
    array (
      'type' => 'accordion',
      'settings' => 
      array (
        'items' => 
        array (
          0 => 
          array (
            'title' => 'Panel title',
            'content' => 'Panel content',
            'open' => false,
          ),
        ),
        'allow_multiple' => false,
      ),
    ),
    'fields' => 
    array (
      'allow_multiple' => 
      array (
        'type' => 'toggle',
        'label' => 'Allow multiple open panels',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'behavior-pack' => 
        array (
          'label' => 'Accordion behavior',
          'description' => 'Expansion behavior for accordion items.',
          'icon' => 'toggle',
          'fields' => 
          array (
            0 => 'allow_multiple',
          ),
        ),
      ),
    ),
  ),
  'tabs' => 
  array (
    'name' => 'Tabs',
    'category' => 'interactive',
    'icon' => 'tab',
    'description' => 'Tabbed content layout.',
    'default' => 
    array (
      'type' => 'tabs',
      'settings' => 
      array (
        'tabs' => 
        array (
          0 => 
          array (
            'title' => 'Tab one',
            'content' => 'Tab content',
          ),
        ),
        'style' => 'line',
        'alignment' => 'left',
      ),
    ),
    'fields' => 
    array (
      'style' => 
      array (
        'type' => 'select',
        'label' => 'Style',
        'options' => 
        array (
          'primary' => 'Primary',
          'secondary' => 'Secondary',
          'outline' => 'Outline',
          'ghost' => 'Ghost',
          'solid' => 'Solid',
          'dashed' => 'Dashed',
          'dotted' => 'Dotted',
          'double' => 'Double',
          'line' => 'Line',
          'boxed' => 'Boxed',
          'pill' => 'Pill',
        ),
      ),
      'alignment' => 
      array (
        'type' => 'select',
        'label' => 'Alignment',
        'options' => 
        array (
          'left' => 'Left',
          'center' => 'Center',
          'right' => 'Right',
        ),
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'layout-pack' => 
        array (
          'label' => 'Tabs layout',
          'description' => 'Tab treatment and alignment.',
          'icon' => 'tab',
          'fields' => 
          array (
            0 => 'style',
            1 => 'alignment',
          ),
        ),
      ),
    ),
  ),
  'modal' => 
  array (
    'name' => 'Modal',
    'category' => 'interactive',
    'icon' => 'window',
    'description' => 'Trigger button and modal content.',
    'default' => 
    array (
      'type' => 'modal',
      'settings' => 
      array (
        'trigger_text' => 'Open modal',
        'title' => 'Modal title',
        'content' => 'Modal content',
        'size' => 'md',
      ),
    ),
    'fields' => 
    array (
      'trigger_text' => 
      array (
        'type' => 'text',
        'label' => 'Trigger text',
      ),
      'title' => 
      array (
        'type' => 'text',
        'label' => 'Title',
      ),
      'content' => 
      array (
        'type' => 'textarea',
        'label' => 'Content',
        'rows' => 6,
      ),
      'size' => 
      array (
        'type' => 'select',
        'label' => 'Size',
        'options' => 
        array (
          'sm' => 'Small',
          'md' => 'Medium',
          'lg' => 'Large',
          'xl' => 'Extra large',
        ),
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'content-pack' => 
        array (
          'label' => 'Modal content',
          'description' => 'Trigger copy and modal body.',
          'icon' => 'window',
          'fields' => 
          array (
            0 => 'trigger_text',
            1 => 'title',
            2 => 'content',
          ),
        ),
        'layout-pack' => 
        array (
          'label' => 'Modal layout',
          'description' => 'Modal size and presentation.',
          'icon' => 'layers',
          'fields' => 
          array (
            0 => 'size',
          ),
        ),
      ),
    ),
  ),
  'tooltip' => 
  array (
    'name' => 'Tooltip',
    'category' => 'interactive',
    'icon' => 'message-circle',
    'description' => 'Inline trigger with contextual helper text.',
    'default' => 
    array (
      'type' => 'tooltip',
      'settings' => 
      array (
        'text' => 'Hover me',
        'content' => 'Tooltip content',
        'position' => 'top',
      ),
    ),
    'fields' => 
    array (
      'text' => 
      array (
        'type' => 'text',
        'label' => 'Text',
      ),
      'content' => 
      array (
        'type' => 'text',
        'label' => 'Content',
      ),
      'position' => 
      array (
        'type' => 'select',
        'label' => 'Position',
        'options' => 
        array (
          'top' => 'Top',
          'bottom' => 'Bottom',
          'left' => 'Left',
          'right' => 'Right',
        ),
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'content-pack' => 
        array (
          'label' => 'Tooltip content',
          'description' => 'Trigger, tooltip copy and placement.',
          'icon' => 'message-circle',
          'fields' => 
          array (
            0 => 'text',
            1 => 'content',
            2 => 'position',
          ),
        ),
      ),
    ),
  ),
  'product-card' => 
  array (
    'name' => 'Product Card',
    'category' => 'ecommerce',
    'icon' => 'shopping-bag',
    'description' => 'Single product card for catalog-like pages.',
    'default' => 
    array (
      'type' => 'product-card',
      'settings' => 
      array (
        'image' => NULL,
        'title' => 'Product name',
        'description' => 'Short product description',
        'price' => 99.99,
        'old_price' => NULL,
        'currency' => '$',
        'rating' => 5,
        'reviews_count' => 10,
        'button_text' => 'Add to cart',
      ),
    ),
    'fields' => 
    array (
      'image' => 
      array (
        'type' => 'media',
        'label' => 'Image',
      ),
      'title' => 
      array (
        'type' => 'text',
        'label' => 'Title',
        'required' => true,
      ),
      'description' => 
      array (
        'type' => 'textarea',
        'label' => 'Description',
        'rows' => 2,
      ),
      'price' => 
      array (
        'type' => 'number',
        'label' => 'Price',
        'required' => true,
        'step' => 0.01,
      ),
      'old_price' => 
      array (
        'type' => 'number',
        'label' => 'Old price',
        'step' => 0.01,
      ),
      'currency' => 
      array (
        'type' => 'text',
        'label' => 'Currency',
        'value' => '$',
      ),
      'rating' => 
      array (
        'type' => 'number',
        'label' => 'Rating',
        'min' => 0,
        'max' => 5,
        'step' => 0.5,
      ),
      'reviews_count' => 
      array (
        'type' => 'number',
        'label' => 'Review count',
      ),
      'button_text' => 
      array (
        'type' => 'text',
        'label' => 'Button text',
      ),
    ),
  ),
  'product-list' => 
  array (
    'name' => 'Product List',
    'category' => 'ecommerce',
    'icon' => 'list',
    'description' => 'Grid or list of product items.',
    'default' => 
    array (
      'type' => 'product-list',
      'settings' => 
      array (
        'products' => 
        array (
        ),
        'columns' => 4,
        'layout' => 'grid',
        'show_rating' => true,
        'show_price' => true,
        'show_add_to_cart' => true,
      ),
    ),
    'fields' => 
    array (
      'products' => 
      array (
        'type' => 'repeater',
        'label' => 'Products',
        'fields' => 
        array (
          0 => 
          array (
            'type' => 'text',
            'key' => 'title',
            'label' => 'Title',
          ),
          1 => 
          array (
            'type' => 'number',
            'key' => 'price',
            'label' => 'Price',
          ),
          2 => 
          array (
            'type' => 'media',
            'key' => 'image',
            'label' => 'Image',
          ),
        ),
      ),
      'columns' => 
      array (
        'type' => 'select',
        'label' => 'Columns',
        'options' => 
        array (
          1 => '1',
          2 => '2',
          3 => '3',
          4 => '4',
          5 => '5',
          6 => '6',
        ),
      ),
      'layout' => 
      array (
        'type' => 'select',
        'label' => 'Layout',
        'options' => 
        array (
          'grid' => 'Grid',
          'list' => 'List',
          'masonry' => 'Masonry',
          'slider' => 'Slider',
          'carousel' => 'Carousel',
          'cards' => 'Cards',
          'minimal' => 'Minimal',
          'vertical' => 'Vertical',
          'horizontal' => 'Horizontal',
          'inline' => 'Inline',
        ),
      ),
      'show_rating' => 
      array (
        'type' => 'toggle',
        'label' => 'Show rating',
      ),
      'show_price' => 
      array (
        'type' => 'toggle',
        'label' => 'Show price',
      ),
      'show_add_to_cart' => 
      array (
        'type' => 'toggle',
        'label' => 'Show add to cart',
      ),
    ),
  ),
  'cart' => 
  array (
    'name' => 'Cart',
    'category' => 'ecommerce',
    'icon' => 'shopping-cart',
    'description' => 'Cart summary placeholder for ecommerce flows.',
    'default' => 
    array (
      'type' => 'cart',
      'settings' => 
      array (
        'items' => 
        array (
          0 => 
          array (
            'title' => 'Starter plan',
            'quantity' => 1,
            'price' => 49,
          ),
          1 => 
          array (
            'title' => 'Support add-on',
            'quantity' => 1,
            'price' => 19,
          ),
        ),
        'currency' => '$',
        'show_coupon' => true,
        'show_shipping' => true,
      ),
    ),
    'fields' => 
    array (
      'items' => 
      array (
        'type' => 'repeater',
        'label' => 'Items',
        'fields' => 
        array (
          0 => 
          array (
            'type' => 'text',
            'key' => 'title',
            'label' => 'Title',
          ),
          1 => 
          array (
            'type' => 'number',
            'key' => 'quantity',
            'label' => 'Quantity',
          ),
          2 => 
          array (
            'type' => 'number',
            'key' => 'price',
            'label' => 'Price',
          ),
        ),
      ),
      'currency' => 
      array (
        'type' => 'text',
        'label' => 'Currency',
      ),
      'show_coupon' => 
      array (
        'type' => 'toggle',
        'label' => 'Show coupon',
      ),
      'show_shipping' => 
      array (
        'type' => 'toggle',
        'label' => 'Show shipping',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'cart-items-pack' => 
        array (
          'label' => 'Cart items',
          'description' => 'Preview line items and pricing rows.',
          'icon' => 'shopping-cart',
          'fields' => 
          array (
            0 => 'items',
            1 => 'currency',
          ),
        ),
        'cart-behavior-pack' => 
        array (
          'label' => 'Cart behavior',
          'description' => 'Coupon and shipping rows.',
          'icon' => 'toggle',
          'fields' => 
          array (
            0 => 'show_coupon',
            1 => 'show_shipping',
          ),
        ),
      ),
    ),
  ),
  'alert' => 
  array (
    'name' => 'Alert',
    'category' => 'utility',
    'icon' => 'info',
    'description' => 'Informational, success, warning or error notice.',
    'default' => 
    array (
      'type' => 'alert',
      'settings' => 
      array (
        'type' => 'info',
        'title' => 'Information',
        'content' => 'Add your notice text here.',
        'closable' => true,
      ),
    ),
    'fields' => 
    array (
      'type' => 
      array (
        'type' => 'select',
        'label' => 'Type',
        'options' => 
        array (
          'disc' => 'Bulleted',
          'decimal' => 'Numbered',
          'none' => 'Unstyled',
          'youtube' => 'YouTube',
          'vimeo' => 'Vimeo',
          'html5' => 'HTML5',
          'info' => 'Info',
          'success' => 'Success',
          'warning' => 'Warning',
          'error' => 'Error',
          'text' => 'Text',
          'email' => 'Email',
          'textarea' => 'Textarea',
          'select' => 'Select',
          'radio' => 'Radio',
          'checkbox' => 'Checkbox',
          'checkbox_group' => 'Checkbox group',
          'number' => 'Number',
          'date' => 'Date',
          'time' => 'Time',
          'hidden' => 'Hidden field',
          'calculator' => 'Calculator',
          'file' => 'File upload',
          'divider' => 'Divider',
          'html' => 'HTML content',
          'total' => 'Total',
          'subtotal' => 'Subtotal',
          'tax' => 'Tax',
          'discount' => 'Discount',
        ),
      ),
      'title' => 
      array (
        'type' => 'text',
        'label' => 'Title',
      ),
      'content' => 
      array (
        'type' => 'textarea',
        'label' => 'Content',
      ),
      'closable' => 
      array (
        'type' => 'toggle',
        'label' => 'Closable',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'content-pack' => 
        array (
          'label' => 'Alert content',
          'description' => 'Alert type, title and message.',
          'icon' => 'info',
          'fields' => 
          array (
            0 => 'type',
            1 => 'title',
            2 => 'content',
          ),
        ),
        'behavior-pack' => 
        array (
          'label' => 'Alert behavior',
          'description' => 'Dismissal behavior.',
          'icon' => 'toggle',
          'fields' => 
          array (
            0 => 'closable',
          ),
        ),
      ),
    ),
  ),
  'progress-bar' => 
  array (
    'name' => 'Progress Bar',
    'category' => 'utility',
    'icon' => 'loader',
    'description' => 'Visual progress indicator.',
    'default' => 
    array (
      'type' => 'progress-bar',
      'settings' => 
      array (
        'value' => 75,
        'max' => 100,
        'color' => '#3b82f6',
        'height' => 8,
        'show_label' => true,
      ),
    ),
    'fields' => 
    array (
      'value' => 
      array (
        'type' => 'number',
        'label' => 'Value',
        'min' => 0,
        'required' => true,
      ),
      'max' => 
      array (
        'type' => 'number',
        'label' => 'Maximum',
        'min' => 1,
        'required' => true,
      ),
      'color' => 
      array (
        'type' => 'color',
        'label' => 'Color',
      ),
      'height' => 
      array (
        'type' => 'number',
        'label' => 'Height',
        'min' => 2,
        'max' => 50,
      ),
      'show_label' => 
      array (
        'type' => 'toggle',
        'label' => 'Show label',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'progress-pack' => 
        array (
          'label' => 'Progress',
          'description' => 'Value range, color and label visibility.',
          'icon' => 'loader',
          'fields' => 
          array (
            0 => 'value',
            1 => 'max',
            2 => 'color',
            3 => 'height',
            4 => 'show_label',
          ),
        ),
      ),
    ),
  ),
  'breadcrumbs' => 
  array (
    'name' => 'Breadcrumbs',
    'category' => 'utility',
    'icon' => 'link',
    'description' => 'Navigation trail separator settings.',
    'default' => 
    array (
      'type' => 'breadcrumbs',
      'settings' => 
      array (
        'items' => 
        array (
          0 => 
          array (
            'title' => 'Home',
            'url' => '/',
          ),
          1 => 
          array (
            'title' => 'Current page',
            'url' => NULL,
          ),
        ),
        'separator' => '/',
      ),
    ),
    'fields' => 
    array (
      'separator' => 
      array (
        'type' => 'text',
        'label' => 'Separator',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'content-pack' => 
        array (
          'label' => 'Breadcrumbs',
          'description' => 'Breadcrumb separator and trail presentation.',
          'icon' => 'link',
          'fields' => 
          array (
            0 => 'separator',
          ),
        ),
      ),
    ),
  ),
  'collapse' => 
  array (
    'name' => 'Collapse',
    'category' => 'utility',
    'icon' => 'chevron-right',
    'description' => 'Single collapsible content block.',
    'default' => 
    array (
      'type' => 'collapse',
      'settings' => 
      array (
        'title' => 'Collapse title',
        'content' => 'Hidden content',
        'open' => false,
      ),
    ),
    'fields' => 
    array (
      'title' => 
      array (
        'type' => 'text',
        'label' => 'Title',
      ),
      'open' => 
      array (
        'type' => 'toggle',
        'label' => 'Open by default',
      ),
    ),
    'editor' => 
    array (
      'packs' => 
      array (
        'content-pack' => 
        array (
          'label' => 'Collapse content',
          'description' => 'Title and default open state.',
          'icon' => 'chevron-right',
          'fields' => 
          array (
            0 => 'title',
            1 => 'open',
          ),
        ),
      ),
    ),
  ),
);

$blocks = array_merge(
    $blocks,
    require __DIR__ . '/breakdance_reference_blocks.php'
);

foreach ($blocks as $type => $config) {
    BlockRegistry::register($type, $config);
}

return $blocks;
