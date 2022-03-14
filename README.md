# Bloomreach_EngagementConnector module

This is a module for integration with the [Bloomreach service](https://www.bloomreach.com/).

## Prerequisites

- Magento 2.3, 2.4
- PHP 7.3, 7.4, 8.1

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

## Additional Data

### Logger

- `<project_dir>/var/log/bloomreach/engagement_connector.log` - contains errors information
- `<project_dir>/var/log/bloomreach/debug.log` - contains debugging information

### Data Mapping

- `Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver` - responsible for map Magento entity data to the Bloomreach data. Returns `Magento\Framework\DataObject`;

#### Configuration File

- `bloomreach_entity_mapping.xml` - allows you to configure field mapping

##### Nodes

- `entity_type` - entity type to map;
- `bloomereach_code` - the name of the key on the Bloomreach side;
- `field` - field to map with `bloomreach_code`. 

#### How to add entity to the Mapping

1. Add entity configuration to the `bloomreach_entity_mapping.xml`.
```xml
<entity_type entity="custom_entity">
    <bloomreach_code code="entity_id">
        <field code="entity_id" />
    </bloomreach_code>
    <bloomreach_code code="created_at">
        <field code="created_at" />
    </bloomreach_code>
    <bloomreach_code code="updated_at">
        <field code="updated_at" />
    </bloomreach_code>
</entity_type>
```
2. Create a class that implements `Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\DataMapperInterface`
3. Pass your Mapper to the `Bloomreach\EngagementConnector\Model\DataMapping\DataMapperFactory` via `di.xml`
```xml
<type name="Bloomreach\EngagementConnector\Model\DataMapping\DataMapperFactory">
    <arguments>
        <argument name="dataMappers" xsi:type="array">
            <item name="custom_entity" xsi:type="string">
                Vendor\Name\Model\DataMapping\DataMapper\Custom
            </item>
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
                <item name="entity_id" xsi:type="object">
                    Vendor\Name\Model\DataMapping\FieldValueRenderer\CustomRenderer\EntityIdRenderer
                </item>
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
            <item name="configurable" xsi:type="object">
                Vendor\Name\Model\DataMapping\DataMapper\Product\Configurable
            </item>
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
                <item name="custom_field" xsi:type="object">
                    Vendor\Name\Model\DataMapping\FieldValueRenderer\Product\CustomRenderer
                </item>
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
                <item name="entity_id" xsi:type="object">
                    Vendor\Name\Model\DataMapping\FieldValueRenderer\Product\Simple\EntityIdRenderer
                </item>
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
                <item name="entity_id" xsi:type="object">
                    Vendor\Name\Model\DataMapping\FieldValueRenderer\Product\\EntityIdRenderer
                </item>
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
    <bloomreach_code code="custom_code">
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

### Observers

``Bloomreach\EngagementConnector\Observer\CustomerEntitySave`` the event ``customer_save_after`` 
Get customer entity after save.

``Bloomreach\EngagementConnector\Observer\OrderEntitySave`` the event ``checkout_submit_all_after``
Get order entity after save

``Bloomreach\EngagementConnector\Observer\ProductEntitySave`` the event ``catalog_product_save_commit_after``
Get product entity after save

### Services

``Bloomreach\EngagementConnector\Service\Export\PrepareCustomerDataService`` Preparing customer entity data after save, 
and push it to the mapper.

``Bloomreach\EngagementConnector\Service\Export\PrepareOrderDataService`` Preparing order entity data after save and 
push it to the mapper.

``Bloomreach\EngagementConnector\Service\Export\PrepareProductDataService`` Prepare product entity data and push it to 
the mapper.

### API

Start the API import: ``Bloomreach\EngagementConnector\Service\Integration\StartApiImportService`` class can be used to 
start the import by API call. The method receives the import ID and path to the csv file with data. Also, the parameter
``test_connection`` can be used to testing.

``Bloomreach\EngagementConnector\Service\Integration\GetEndpointService`` class responsible to preparing URL endpoint
by system config.

### Console

``Bloomreach\EngagementConnector\Console\Command\AddToExportQueue`` can be used to add entities to export queue manually.
The command:
```bash
bin/magento bloomreach:add-to-export-queue
```

``Bloomreach\EngagementConnector\Console\Command\StartExport`` can be used to start export manually.
The command:
```bash
bin/magento bloomreach:export-start --import_id="*********" --csv_file_path="*****" --test_connection="1"
```

Using the options, you can run the export from a specific csv file.

Where:
- import_id - is ID of necessary import, can be clarify in the bloomreach exponea admin
- csv_file_path - path of CSV file
- test_connection - is not required parameter - set "1" to run rest connection.

``Bloomreach\EngagementConnector\Console\Command\GenerateExportFileForExportQueue`` can be used to testing or to generating import files manually.
The command:
```bash
bin/magento bloomreach:generate-export-files --entity_type="*********" --update_export_status="1"
```
Where:
- entity_type - is not a required parameter. If not specified, an export file is generated for all entity types. For example: `catalog_product`
- update_export_status - is not required parameter. Set `1` to update the status of the export queue item after creating the file.


## Cron

- `bloomreach_add_to_export_queue` - prepares entities waiting to be exported and adds them to the export queue.
- `bloomreach_run_export` - exports data from the export queue to the Bloomreach service.

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
            <item name="catalog_product" xsi:type="string">catalog_product</item>
        </argument>
    </arguments>
</type>
```
2. Create Data Mapping for the new entity.
3. Pass collection class to the `Bloomreach\EngagementConnector\Model\Export\Entity\CollectionFactory` via `di.xml`:
```xml
<type name="Bloomreach\EngagementConnector\Model\Export\Entity\CollectionFactory">
    <arguments>
        <argument name="collections" xsi:type="array">
            <item name="catalog_product" xsi:type="string">
                Magento\Catalog\Model\ResourceModel\Product\Collection
            </item>
        </argument>
    </arguments>
</type>
```
4. Create class that implements `Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface`.
5. **Optional** If you want to use a separate API to send data to the Bloomreach, you can implement other transporter. Pass your transporter class to the `Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver` via `di.xml` and specify the `entity_type` and `api_type` as name of items:
```xml
<type name="Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver">
    <arguments>
        <argument name="transporters" xsi:type="array">
            <item name="csv_export" xsi:type="array">
                <item name="default" xsi:type="object">
                    Bloomreach\EngagementConnector\Model\Export\Transporter\InitialExport\DefaultTransporter
                </item>
            </item>
        </argument>
    </arguments>
</type>
```
6. Use a `Bloomreach\EngagementConnector\Model\Export\Entity\AddToExport` class to add your entity id to the export.

#### How to add data to the export if you cannot create a collection for your entity?

1. Create Data Mapping for the new entity.
2. Use a `Bloomreach\EngagementConnector\Model\Export\Queue\AddDataToExportQueue` class to add your entity to the export queue
3. Create class that implements `Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface`.
4. **Optional** If you want to use separate API ot send data to the Bloomreach, you can implement other transporter. Pass your transporter class to the `Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver` via `di.xml` and specify the `entity_type` and `api_type` as name of items:
```xml
<type name="Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver">
    <arguments>
        <argument name="transporters" xsi:type="array">
            <item name="csv_export" xsi:type="array">
                <item name="default" xsi:type="object">
                    Bloomreach\EngagementConnector\Model\Export\Transporter\InitialExport\DefaultTransporter
                </item>
            </item>
        </argument>
    </arguments>
</type>
```

#### How to add your entity to the export preconfiguration:

1. Create a class that implements `Bloomreach\EngagementConnector\Model\ExportPreconfiguration\PreconfigurateEntityExportInterface`
2. Pass your class to the `Bloomreach\EngagementConnector\Model\ExportPreconfiguration\PreconfigurateEntityExport` via `di.xml`:
```xml
<type name="Bloomreach\EngagementConnector\Model\ExportPreconfiguration\PreconfigurateEntityExport">
    <arguments>
        <argument name="entitiesToPreconfigurate" xsi:type="array">
            <item name="catalog_product" xsi:type="object">
                Bloomreach\EngagementConnector\Model\ExportPreconfiguration\Entity\Product
            </item>
        </argument>
    </arguments>
</type>
```

#### How to add your entity to the initial export:

1. Create a class that implements `Bloomreach\EngagementConnector\Model\InitialExport\InitialEntityExportInterface`.
2. Pass your class to the `Bloomreach\EngagementConnector\Model\InitialExport\InitialEntityExport` via `di.xml`:
```xml
<type name="Bloomreach\EngagementConnector\Model\InitialExport\InitialEntityExport">
    <arguments>
        <argument name="entitiesToExport" xsi:type="array">
            <item name="catalog_product" xsi:type="object">
                Bloomreach\EngagementConnector\Model\InitialExport\Entity\Product
            </item>
        </argument>
    </arguments>
</type>
```
3. Create system config for import id
4. Pass your config to the class via `di.xml`:
```xml
<type name="Bloomreach\EngagementConnector\Service\Integration\ImportIdResolver">
    <arguments>
        <argument name="importIdsConfigPath" xsi:type="array">
            <item name="catalog_product" xsi:type="string">bloomreach_engagement/general/catalog_import_id</item>
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
                <item name="custom_entity" xsi:type="object">
                    Bloomreach\EngagementConnector\Model\Export\Transporter\Event\CustomEntityTransporter
                </item>
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
5. Use `Bloomreach_EngagementConnector::tracking/event/default.phtml` template for send event
```xml
<referenceBlock name="bloomreach.engagement.connector.tracking">
    <block class="Bloomreach\EngagementConnector\Block\Tracking\Event"
           name="bloomreach.engagement.connector.custom.event.tracking"
           after="-"
           template="Bloomreach_EngagementConnector::tracking/event/default.phtml">
        <arguments>
            <argument name="events" xsi:type="object">
                Bloomreach\EngagementConnector\Model\Tracking\Event\ProductPage\ViewItem
            </argument>
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
