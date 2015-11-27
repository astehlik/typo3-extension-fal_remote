# Remote TYPO3 FAL adapter

fal_remote is a TYPO3 Extension that provides a remote adapter the TYPO3
File Abstraction Layer (FAL).

## What is it good for?

This adapter can be used in development or testing systems where you want to
use the contents from the live system.

## How to use it

1. Install the Extension (just clone it, not yet in TER) on the client (dev) and the server (production).
2. Adjust the Extension configuration of fal_dummy:
 * On the remote: enable the Server functionality
 * On the client: configure the URL to the remote TYPO3 installation.
3. Set the driver of your storage(s) to "Remote"
4. In the storage you need to set an alternative processing folder in a storage
   that allows write access (e.g. 0:/typo3temp/_processed_/). The folder **must**
   exist on the file system.

**Important!** The encryption keys must be identical on both TYPO3 instances,
otherwise the hash validation will fail.