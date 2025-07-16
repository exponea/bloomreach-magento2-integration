# Bloomreach_EngagementConnector module

This is a module for integration with the [Bloomreach service](https://www.bloomreach.com/).

## Prerequisites

- Magento 2.4
- PHP 7.3, 7.4, 8.1, 8.2, 8.3, 8.4

## Installation

### Composer

1. composer config repositories.bloomreach git https://github.com/exponea/bloomreach-magento2-integration.git
2. composer require bloomreach/bloomreach-engagement-connector-magento

### Manually

Check to see if your Magento instance has an app/code directory structure. If not, create it manually.

1. Then create 2 more directories with this path: `Bloomreach/EngagementConnector`. The final path should look like: `app/code/Bloomreach/EngagementConnector`
2. Extract the downloaded zip folder and move the content of the extracted folder to this path: `app/code/Bloomreach/EngagementConnector`

### Initialize the extension

#### For production mode stores

1. bin/magento maintenance:enable
2. bin/magento module:enable Bloomreach_EngagementConnector
3. bin/magento deploy:mode:set production
4. bin/magento cache:clean
5. bin/magento maintenance:disable

#### For developer mode stores

1. bin/magento module:enable Bloomreach_EngagementConnector
2. bin/magento setup:upgrade
3. bin/magento cache:clean

## Database modification

### Created Tables

- `bloomreach_export_queue` - contains a queue of prepared data waiting to be exported.
- `bloomreach_export_entity` - contains the identifiers of the entities to be added to the export queue.
- `bloomreach_initial_export_status` - stores the initial export data (Current export status, export progress, export errors)

## Cron

- `bloomreach_add_to_export_queue` - prepares the entities that are waiting to be exported and adds them to the export queue.
- `bloomreach_run_export` - exports data from the export queue to the Bloomreach service.
- `bloomreach_clean_export_queue` - cleans export queue data from database.
- `bloomreach_clean_csv` - cleans csv export files.
- `bloomreach_export_queue_error_notification` - sends export error notifications.

## Console Commands

### Add entities to the export queue Command

- Responsible class:
``Bloomreach\EngagementConnector\Console\Command\AddToExportQueue``
- The command:
```bash
bin/magento bloomreach:add-to-export-queue
```

### Start Export Command

- Responsible class:
``Bloomreach\EngagementConnector\Console\Command\StartExport``.
The command:
```bash
bin/magento bloomreach:export-start --import_id="*********" --csv_file_path="*****" --test_connection="1"
```

Using the options, you can run the export from a specific csv file.

Where:
- import_id - ID of necessary import, can be taken from the Bloomreach
- csv_file_path - path of the CSV file
- - test_connection - set "1" to run a test connection (not required)

### Clean CSV Files Command

- Responsible class:
  ``Bloomreach\EngagementConnector\Console\Command\CleanCsvFilesCommand``
- The command:
```bash
bin/magento bloomreach:clean-csv-files
```

### Clean Export Queue Command
- Responsible class:
  ``Bloomreach\EngagementConnector\Console\Command\CleanExportQueueCommand``
- The command:
```bash
bin/magento bloomreach:clean-export-queue
```

## Uninstallation

### Composer

If the module was installed through composer, it can be uninstalled using the magento uninstall command:
```bash
bin/magento module:uninstall Bloomreach_EngagementConnector
```

### Manually

If the module was installed manually, then you need to do the following steps to completely remove all data:

#### For production mode stores

1. bin/magento maintenance:enable
2. bin/magento module:disable Bloomreach_EngagementConnector
3. delete `MAGENTO_ROOT/app/code/Bloomreach/EngagementConnector` folder;
4. delete `MAGENTO_ROOT/var/bloomreach` folder;
5. delete `bloomreach_export_queue`, `bloomreach_export_entity`, `bloomreach_initial_export_status` tables from database;
6. delete system configs `WHERE path LIKE 'bloomreach_engagement%'`
7. bin/magento setup:upgrade
8. bin/magento deploy:mode:set production
9. bin/magento cache:clean
10. bin/magento maintenance:disable

#### For developer mode stores

1. bin/magento module:disable Bloomreach_EngagementConnector
2. delete `MAGENTO_ROOT/app/code/Bloomreach/EngagementConnector` folder;
3. delete `MAGENTO_ROOT/var/bloomreach` folder;
4. delete `bloomreach_export_queue`, `bloomreach_export_entity`, `bloomreach_initial_export_status` tables from database;
5. delete system configs `WHERE path LIKE 'bloomreach_engagement%'`
6. bin/magento setup:upgrade
7. bin/magento cache:clean

## Additional Data

### Logger

- `MAGENTO_ROOT/var/log/bloomreach/engagement_connector.log` - contains errors information
- `MAGENTO_ROOT/var/log/bloomreach/debug.log` - contains debugging information

### Initial Export Statuses

#### DISABLED
- Value: 1
- Label: DISABLED
- Initial Export has the DISABLED status if a certain feed is disabled in the system configuration.

#### NOT READY
- Value: 2
- Label: NOT READY
- Initial Export has the NOT READY status if a certain feed is enabled in the system configuration and import_id setting is not specified.

#### READY
- Value: 3
- Label: READY
- Initial Export has the READY status if a certain feed is enabled in the system configuration and import_id setting is specified and export has not started yet.

#### SCHEDULED
- Value: 4
- Label: SCHEDULED
- Initial Export has the SCHEDULED status if a certain feed is enabled in the system configuration and import_id setting is specified and Start action was triggered.

#### PROCESSING
- Value: 5
- Label: PROCESSING
- Initial Export has the PROCESSING status if a certain feed is enabled in the system configuration and import_id setting is specified and export has started.

#### ERROR
- Value: 6
- Label: ERROR
- Initial Export has the ERROR status if a certain feed is enabled in the system configuration and import_id setting is specified and export has finished with errors.

#### SUCCESS
- Value: 7
- Label: SUCCESS
- Initial Export has the SUCCESS status if a certain feed is enabled in the system configuration and import_id setting is specified and export has finished successfully.

### Export Queue Statuses

#### PENDING
- Value: 1
- Label: PENDING
- Queue Item has the PENDING status if an item has not sent yet.
- 
#### IN PROGRESS
- Value: 2
- Label: IN PROGRESS
- Queue Item has the IN PROGRESS status if sending is in progress.

#### ERROR
- Value: 3
- Label: ERROR
- Queue Item has the ERROR status f an error occurred while sending.

#### COMPLETE
- Value: 4
- Label: COMPLETE
- Queue Item has the COMPLETE status if an item was sent successfully.

### Data Mapping

- `Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver` - responsible for map Magento entity data to the Bloomreach data. Returns `Magento\Framework\DataObject`;

#### Configuration File

- `bloomreach_entity_mapping.xml` - allows you to configure field mapping

##### Nodes

- `entity_type` - entity type to map;
- `bloomreach_code` - the name of the key on the Bloomreach side;
- `field` - field to map with `bloomreach_code`. 

#### How to add entity to the Mapping

1. Add entity configuration to the `bloomreach_entity_mapping.xml`.
```xml
<entity_type entity="custom_entity">
    <bloomreach_code code="entity_id" type="number">
        <field code="entity_id" />
    </bloomreach_code>
    <bloomreach_code code="active" type="boolean">
      <field code="status"/>
    </bloomreach_code>
    <bloomreach_code code="description" type="long text">
      <field code="short_description"/>
    </bloomreach_code>
    <bloomreach_code code="created_at" type="string">
        <field code="created_at" />
    </bloomreach_code>
    <bloomreach_code code="categories_ids" type="list">
      <field code="categories_ids"/>
    </bloomreach_code>
</entity_type>
```
2. Create a class that implements `Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\DataMapperInterface`
3. Pass your Mapper to the `Bloomreach\EngagementConnector\Model\DataMapping\DataMapperFactory` via `di.xml`
```xml
<type name="Bloomreach\EngagementConnector\Model\DataMapping\DataMapperFactory">
    <arguments>
        <argument name="dataMappers" xsi:type="array">
            <item name="custom_entity" xsi:type="string">Vendor\Name\Model\DataMapping\DataMapper\Custom</item>
        </argument>
    </arguments>
</type>
```
**Optional**. If you want to use a separate renderer for the field you need:
- Create a class that implements `Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface`
- Pass your Renderer to the `Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRendererResolver` via `di.xml` and specify the entity type and field code:
```xml
<type name="Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRendererResolver">
    <arguments>
        <argument name="fieldValueRenderers" xsi:type="array">
            <item name="custom_entity" xsi:type="array">
                <item name="entity_id" xsi:type="object">Vendor\Name\Model\DataMapping\FieldValueRenderer\CustomRenderer\EntityIdRenderer</item>
            </item>
        </argument>
    </arguments>
</type>
```
- Use `Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRendererResolver` in the mapper class to get field value.

#### How to change the mapper for a specific product type

1. Create a class that implements `Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\DataMapperInterface`
2. Pass your Mapper to the `Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\ProductMapperResolver` via `di.xml` and specify `productTypeId` as the name of the argument
```xml
<type name="Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\ProductMapperResolver">
    <arguments>
        <argument name="dataMappers" xsi:type="array">
            <item name="configurable" xsi:type="object">Vendor\Name\Model\DataMapping\DataMapper\Product\Configurable</item>
        </argument>
    </arguments>
</type>
```

#### How to add custom logic for field rendering

1. Create a class that implements `Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface`
2. Pass your Renderer to the `Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRendererResolver` via `di.xml` and specify the `entity_type` and `field` code as name of items:
```xml
<type name="Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRendererResolver">
    <arguments>
        <argument name="fieldValueRenderers" xsi:type="array">
            <item name="catalog_product" xsi:type="array">
                <item name="custom_field" xsi:type="object">Vendor\Name\Model\DataMapping\FieldValueRenderer\Product\CustomRenderer</item>
            </item>
        </argument>
    </arguments>
</type>
```

#### How to add custom logic for field rendering for specific product type

1. Create a class that implements `Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface`
2. Pass your Renderer to the `Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product\ProductTypeRendererResolver` via `di.xml` and specify the `productTypeId` and `field` code as name of items:
```xml
<type name="Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product\ProductTypeRendererResolver">
    <arguments>
        <argument name="fieldValueRenderers" xsi:type="array">
            <item name="simple" xsi:type="array">
                <item name="entity_id" xsi:type="object">Vendor\Name\Model\DataMapping\FieldValueRenderer\Product\Simple\EntityIdRenderer</item>
            </item>
        </argument>
    </arguments>
</type>
```

#### How to add custom logic for field rendering for all products type

1. Create a class that implements `Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface`
2. Pass your Renderer to the `Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRendererResolver` via `di.xml` and specify the `entity_type` and `field` code as name of items:
```xml
<type name="Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRendererResolver">
    <arguments>
        <argument name="fieldValueRenderers" xsi:type="array">
            <item name="catalog_product" xsi:type="array">
                <item name="entity_id" xsi:type="object">Vendor\Name\Model\DataMapping\FieldValueRenderer\Product\EntityIdRenderer</item>
            </item>
        </argument>
    </arguments>
</type>
```

#### How to add a new field to entity mapping

1. Create `bloomreach_entity_mapping.xml` in file in your module.
2. Add a new `bloomreach_code` node to `entity_type` that you want to extend
```xml
<entity_type entity="catalog_product">
    <bloomreach_code code="custom_code" type="string">
        <field code="custom_field" />
    </bloomreach_code>
</entity_type>
```

#### How to exclude `bloomreach_code` from mapping

1. Create `bloomreach_entity_mapping.xml` in file in your module.
2. Add `disabled` equal `true` to `bloomreach_code` that you want to exclude from mapping
```xml
<entity_type entity="catalog_product">
    <bloomreach_code code="entity_id" disabled="true" />
</entity_type>
```

#### How to change a mapping field:

1. Create `bloomreach_entity_mapping.xml` in file in your module.
2. Add a `field` node to `bloomreach_code` node that you want to change
```xml
<entity_type entity="catalog_product">
    <bloomreach_code code="title">
        <field code="custom_field" />
    </bloomreach_code>
</entity_type>
```

#### How to change a field type:

1. Create `bloomreach_entity_mapping.xml` in file in your module.
2. Add `type` attribute to `bloomreach_code` that you want to update
```xml
<entity_type entity="catalog_product">
    <bloomreach_code code="title" type="long text" />
</entity_type>
```

### Export Processes

- `Bloomreach\EngagementConnector\Model\Export\QueueProcessor` - obtains the entities that need to be exported, prepares them and adds them to the export queue.
- `Bloomreach\EngagementConnector\Model\Export\ExportProcessor` - obtaining data from export queue and send them to the Bloomreach service.
- `Bloomreach\EngagementConnector\Model\Export\File\DirectoryProvider` - allows to create and retrieve a directory where the export file should be placed.
- `Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver` - sends data to a specific endpoint, for a specific entity type, to the Bloomreach.

#### How to change the directory where the export file is located?

1. Pass directory name to the `Bloomreach\EngagementConnector\Model\Export\File\DirectoryProvider` via `di.xml` and specify the `entity_type` as name of items:
```xml
<type name="Bloomreach\EngagementConnector\Model\Export\File\DirectoryProvider">
    <arguments>
        <argument name="entityDirectories" xsi:type="array">
            <item name="catalog_product" xsi:type="string">catalog_product</item>
        </argument>
    </arguments>
</type>
```

#### How to add new entity to the export:

1. Add your entity type to the Entity Provider:
```xml
<type name="Bloomreach\EngagementConnector\Model\DataProvider\EntityType">
    <arguments>
        <argument name="entityTypes" xsi:type="array">
            <item name="catalog_product" xsi:type="string">Product Feed</item>
        </argument>
    </arguments>
</type>
```
2. Create Data Mapping for the new entity.
3. **Optional** If you add an event to the export, you will need to add register fields by which Bloomreach recognizes which customer this event belongs to. 
- Add `customer_id` and `email_id` fields to the mapping configuration.
```xml
<entity_type entity="purchase">
    <bloomreach_code code="email_id">
        <field code="customer_email"/>
    </bloomreach_code>
    <bloomreach_code code="customer_id">
        <field code="customer_id"/>
    </bloomreach_code>
</entity_type>
```
- Make sure your entity contains these fields. (If not, implement them using the DataMapper approach).
- Use `Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\RegisteredMapper` in your `DataMapper` to map registered fields.
4. Pass collection class to the `Bloomreach\EngagementConnector\Model\Export\Entity\CollectionFactory` via `di.xml`:
```xml
<type name="Bloomreach\EngagementConnector\Model\Export\Entity\CollectionFactory">
    <arguments>
        <argument name="collections" xsi:type="array">
            <item name="catalog_product" xsi:type="string">Magento\Catalog\Model\ResourceModel\Product\Collection</item>
        </argument>
    </arguments>
</type>
```
4. Add you entity type to the `Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\BuilderComposite` via `di.xml` and specify needed request builder:
```xml
<type name="Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\BuilderComposite">
    <arguments>
        <argument name="builderPool" xsi:type="array">
            <item name="catalog_product" xsi:type="string">Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Request\Entity\Catalog</item>
        </argument>
    </arguments>
</type>
```
5. Create a system config `feed_enable`,
6. Create a system config for `import_id`,
7. **Optional** Create a system config for `catalog_id` if your entity is a catalog,
8. Pass your configs to the `ConfigPathGetter` class via `di.xml`:
```xml
<type name="Bloomreach\EngagementConnector\System\ConfigPathGetter">
    <arguments>
        <argument name="configPool" xsi:type="array">
            <item name="catalog_product" xsi:type="array">
                <item name="feed_enabled" xsi:type="string">bloomreach_engagement/catalog_product_feed/enabled</item>
                <item name="import_id" xsi:type="string">bloomreach_engagement/catalog_product_feed/import_id</item>
                <item name="catalog_id" xsi:type="string">bloomreach_engagement/catalog_product_feed/catalog_id</item>
            </item>
        </argument>
    </arguments>
</type>
```

#### How to change the API endpoint for a specific API call:
1. Create class that implements `Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface`.
2. Pass your transporter class to the `Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver` via `di.xml` and specify the `entity_type` and `api_type` as name of items:
```xml
<type name="Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver">
    <arguments>
        <argument name="transporters" xsi:type="array">
            <item name="csv_export" xsi:type="array">
                <item name="default" xsi:type="object">Bloomreach\EngagementConnector\Model\Export\Transporter\InitialExport\DefaultTransporter</item>
            </item>
        </argument>
    </arguments>
</type>
```

#### How to create an event on Backend:

1. Create a data mapper for your entity type.
2. Use a `Bloomreach\EngagementConnector\Model\Export\Queue\AddEventToExportQueue` class to add you event to export queue.
3. **Optional** If you want to use a separate API to send data to the Bloomreach, you can implement other transporter. Pass your transporter class to the `Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver` via `di.xml` and specify the `entity_type` and `api_type` as name of items:
```xml
<type name="Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver">
    <arguments>
        <argument name="transporters" xsi:type="array">
            <item name="event" xsi:type="array">
                <item name="custom_entity" xsi:type="object">Bloomreach\EngagementConnector\Model\Export\Transporter\Event\CustomEntityTransporter</item>
            </item>
        </argument>
    </arguments>
</type>
```
4. **Optional** If you want to send event using batch api endpoint, pass entity type to `Bloomreach\EngagementConnector\Model\Export\Queue\Batch\CommandNameGetter`:
```xml
<type name="Bloomreach\EngagementConnector\Model\Export\Queue\Batch\CommandNameGetter">
    <arguments>
        <argument name="commandNamePool" xsi:type="array">
            <item name="event" xsi:type="array">
                <item name="purchase" xsi:type="string">customers/events</item>
                <item name="purchase_item" xsi:type="string">customers/events</item>
            </item>
        </argument>
    </arguments>
</type>
```
### Frontend Tracking

#### Event structure:

```json
{
  "eventName": "view_item",
  "eventBody": {
    "sku": "WS02",
    "price": "120.00"
  }
}
```

#### How to send event

1. Prepare event object:
```json
{
  "eventName": "view_item",
  "eventBody": {
    "sku": "WS02",
    "price": "120.00"
  }
}
```
2. Use `Bloomreach_EngagementConnector/js/tracking/event-sender` component to send an event.

#### How to create an event on the Backend side and send after the page loads

1. Create a class that implements `\Bloomreach\EngagementConnector\Model\Tracking\Event\EventsInterface` and `\Magento\Framework\View\Element\Block\ArgumentInterface` interfaces.
2. Create child block for `bloomreach.engagement.connector.tracking` in the layout. 
3. Use `Bloomreach\EngagementConnector\Block\Tracking\Event` class for event block. 
4. Pass your event class to event block via arguments with name `events`.
5. Use `Bloomreach_EngagementConnector::tracking/event/default.phtml` template for send event.
```xml
<referenceBlock name="bloomreach.engagement.connector.tracking">
    <block class="Bloomreach\EngagementConnector\Block\Tracking\Event"
           name="bloomreach.engagement.connector.custom.event.tracking"
           after="-"
           template="Bloomreach_EngagementConnector::tracking/event/default.phtml">
        <arguments>
            <argument name="events" xsi:type="object">Bloomreach\EngagementConnector\Model\Tracking\Event\ProductPage\ViewItem</argument>
        </arguments>
    </block>
</referenceBlock>
```

### How to send event after cart update

1. Create a class that implements `\Bloomreach\EngagementConnector\Model\Tracking\Event\EventsInterface` interface.
2. Pass your class to the `Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventsCollector` class via `di.xml`:
```xml
<type name="Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventsCollector">
    <arguments>
        <argument name="eventsList" xsi:type="array">
            <item name="cart_update" xsi:type="object">Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdate</item>
        </argument>
    </arguments>
</type>
```
