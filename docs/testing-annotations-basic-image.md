# Testing basic image annotations

## Insatllation
* Install [context] (https://www.drupal.org/project/context) drupal module
* Install [context_addassets] (https://www.drupal.org/project/context_addassets) drupal module
* Install [islandora_context] (https://github.com/mjordan/islandora_context) drupal module

When you isntall this module, you will get a Notices such as "Notice: Use of undefined constant ISLANDORA_REST_OBJECT_GET_PERM - assumed 'ISLANDORA_REST_OBJECT_GET_PERM'".  Please ignore that for now.  There is an issue created with the module about this.

* Install [islandora_web_annotations] (https://github.com/digitalutsc/islandora_web_annotations) drupal module
* Go to localhost:8000/islandora_web_annotations/install to install the required islandora content model objects

## Creating the context needed by basic_image annotations
Go to admin/structure/context and import the following context.

```php
$context = new stdClass();
$context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
$context->api_version = 3;
$context->name = 'basic_image';
$context->description = '';
$context->tag = '';
$context->conditions = array(
  'islandora_context_condition_collection_member' => array(
    'values' => array(
      0 => TRUE,
    ),
    'options' => array(
      'islandora_collection_membership' => array(
        'islandora:sp_basic_image_collection' => 'islandora:sp_basic_image_collection',
        'islandora:entity_collection' => 0,
        'islandora:audio_collection' => 0,
        'islandora:bookCollection' => 0,
        'islandora:sp_disk_image_collection' => 0,
        'islandora:newspaper_collection' => 0,
        'islandora:compound_collection' => 0,
        'islandora:sp_large_image_collection' => 0,
        'islandora:video_collection' => 0,
        'islandora:sp_web_archive_collection' => 0,
        'islandora:sp_pdf_collection' => 0,
        'ir:citationCollection' => 0,
        'islandora:oralhistories_collection' => 0,
      ),
    ),
  ),
);
$context->reactions = array(
  'css_path' => array(
    'sites/all/libraries/web-annotaions/libs/annotorious/js' => array(),
    'sites/all/libraries/web-annotaions/libs/annotorious/css' => array(),
    'sites/all/modules/islandora_web_annotations/lib' => array(
      'sites/all/modules/islandora_web_annotations/lib/css/annotorious.css' => 'sites/all/modules/islandora_web_annotations/lib/css/annotorious.css',
    ),
    'sites/all/modules/islandora_web_annotations/css' => array(
      'sites/all/modules/islandora_web_annotations/css/base/base.css' => 'sites/all/modules/islandora_web_annotations/css/base/base.css',
      'sites/all/modules/islandora_web_annotations/css/basic_image/basic_image.css' => 'sites/all/modules/islandora_web_annotations/css/basic_image/basic_image.css',
    ),
  ),
  'js_path' => array(
    'sites/all/libraries/web-annotaions/libs/annotorious/js' => array(),
    'sites/all/modules/islandora_web_annotations/lib' => array(
      'sites/all/modules/islandora_web_annotations/lib/js/annotorious.min.js' => 'sites/all/modules/islandora_web_annotations/lib/js/annotorious.min.js',
    ),
    'sites/all/modules/islandora_web_annotations/js' => array(
      'sites/all/modules/islandora_web_annotations/js/base/base.js' => 'sites/all/modules/islandora_web_annotations/js/base/base.js',
      'sites/all/modules/islandora_web_annotations/js/basic_image/basic_image.js' => 'sites/all/modules/islandora_web_annotations/js/basic_image/basic_image.js',
    ),
  ),
);
$context->condition_mode = 0;
```

## Create a sample image and see if you are able to create annotations
Review [annotorious] (https://annotorious.github.io/)  for detail feature list.

