<?php
defined('TYPO3_MODE') or die();

// Register a DataHandler hook to always set the value of the original record to the translations
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['l10nexclude'] = \CMSExperts\L10nexclude\Hooks\DataHandlerTranslationFieldsUpdater::class;

// Register a command controller to migrate existing data
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = \CMSExperts\L10nexclude\Controller\TranslationCommandController::class;
