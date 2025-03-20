# v1.1.3

- Fixes
  - Resolved an issue where system notifications were still displayed when the extension was disabled
- Additions
  - Added a new setting to enable or disable system notifications. This setting is enabled by default
- Improvements
  - Optimized system notification logic by implementing a 10-minute caching mechanism to reduce redundant executions when admin panel pages are loaded
  - 
# v1.1.2

- Changes
  - The default store view value is now used for category and product names in tracking, ensuring consistent and accurate data representation across reports
  - Introduced the ability to set field types in `bloomreach_entity_mapping.xml`. All fields now have predefined types, enabling better standardization and control over entity mapping
- Fixes
  - Fixed an issue where an error occurred when the SKU was missing for order items

# v1.1.1

- Fixes
  - Resolved issues with configuring the `Customers Feed` import
  - Corrected the order of category levels
  - Fixed incorrect value types for fields such as price, qty, and others
  - Resolved an issue where `customer_id` and `email_id` were not tracked when a new customer was created
- Additions
  - Added compatibility with `PHP 8.3`
  - Added compatibility with Magento `2.4.7-p3`
  - Introduced the ability to configure searchable fields for `Products Feed` and `Variants Feed`
  - Added the `sku` field to the `Purchase Items Feed`
- Improvements
  - Enhanced the `Export Queue Clean Up` cron job
  - Enhanced the `Progress Log` in the Initial Import Grid
  - Improved the module uninstallation process by ensuring module log files are deleted
- Changes
  - Discontinued compatibility with Magento versions lower than `2.4.0`
  - Country names are now tracked in English instead of being translated

# v1.1.0

- Fixed guest cart merging after logging in as customer
- Improved the performance for processing large amount of orders

# v1.0.0

- Implemented new UI for Initial Import configuration
- Implemented new UI for Export Queue
- Added different types of ACL resources.
- Added the ability to download log files on the Export Queue page
- Added new system configurations
- Added the ability to enable/disable real time updates for each feed type separately
- Added the ability to enable/disable each type of feed separately
- Added the ability to change registered mapping
- Added the ability to track frontend events to the dataLayer
- Added the ability to notify the user by e-mail about errors
- Performance improvements

# v0.9.6

- Improved the sending flow, by skipping the entities that are not configured
- Fixed the PayPal orders are not being synced
- Added the Admin system notification if some errors were thrown while the import is processing
- Fixed catalog updates are not being sent
- Added PHP 8.2 compatibility
- Added Magento 2.4.6 compatibility

# v0.9.5

- Improved the retry mechanism using the "exponential backoff" with jitter
- Fixed the exception being thrown when uninstalling a fresh installed module
- Fixed the separator for prices higher than $1000
- Added a button to validate the entered credentials

# v0.9.4

- Improved the queue failed retry mechanism
- Added module's clearing configs and all related data, on uninstalling
- Added customer anonymization after its deletion from Magento
- Added debugging mode
- Added the prevention to avoid triggering the imports multiple times

# v0.9.3

- Added PHP 8.1 compatibility

# v0.9.2

- Init module
