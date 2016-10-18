# Localization Helper Extension for TYPO3

This TYPO3 extension ensures a transparent way for all translated records within a TYPO3 installation
to have the same values set as their original record, to make sure the fields.

The goals of this extension:
 * Don't worry about common "overlay" problems.
 * Have a good chance of getting to a clear point how the data is stored in the database (less "magic").

Under the hood, it checks for all fields of a record that have the configuration option
`l10n_mode` set to `exclude` to contain the same values through all translations.

## Requirements

A supported TYPO3 installation, that is TYPO3 v6.2 or higher.

## Installation

Install the extension via TER (extension key "l10nexclude") or composer (`composer require cmsexperts/l10nexclude`)
and make sure the extension is activated. This can be checked in the Extension Manager of the running TYPO3 installation.

If you install this extension in a running TYPO3 instance with already translated records,
there is a Extbase command controller to update all existing translations.

This can be triggered via `./typo3/cli_dispatch extbase translation:syncFields`.

## Contribute

Maintenance of the TYPO3 extension is handled by the CMS experts through this GitHub Repository.

Feel free to put any pull request to the repository, or put ideas in the issue tracker.

## Credits

* Benni Mack