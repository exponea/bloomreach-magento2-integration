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
