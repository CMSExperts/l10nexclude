<?php
namespace CMSExperts\L10nexclude\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * A Command Controller which provides a migration for all existing fields in TCA
 * that have l10n_mode=exclude set, where all translations are filled with the original language values.
 */
class TranslationCommandController extends CommandController
{
    /**
     * Migrates existing records,
     * does include deleted and hidden records!
     */
    public function syncFieldsCommand()
    {
        $tables = array_keys($GLOBALS['TCA']);
        foreach ($tables as $tableName) {
            if (!BackendUtility::isTableLocalizable($tableName)) {
                $this->outputLine('No translation information for database table "' . $tableName . '"" found, skipping.');
                continue;
            }
            $languageFieldName = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'];
            $originalPointerFieldName = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'];

            $overlaidFieldNames = $this->getFieldsExcludedForTranslations($tableName);

            if (empty($overlaidFieldNames)) {
                $this->outputLine('No l10n-excluded fields for database table "' . $tableName . '"" found, skipping.');
                continue;
            }

            // Fetch all original records (no translations)
            $originalRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
                '*',
                $tableName,
                $languageFieldName . '=0'
            );
            $this->outputLine('Found ' . count($originalRecords) . ' records from table "' . $tableName . '".');

            // Loop over all records and update their translations
            $updatedTranslations = 0;
            foreach ($originalRecords as $originalRecord) {
                $updateDataForTranslations = [];
                foreach ($overlaidFieldNames as $fieldName) {
                    $updateDataForTranslations[$fieldName] = $originalRecord[$fieldName];
                }
                $this->getDatabaseConnection()->exec_UPDATEquery(
                    $tableName,
                    $originalPointerFieldName . '=' . (int)$originalRecords['uid'],
                    $updateDataForTranslations
                );
                $updatedTranslations += $this->getDatabaseConnection()->sql_affected_rows();
            }
            $this->outputLine('Updated ' . $updatedTranslations . ' translations (' . count($overlaidFieldNames) . ' fields each) in the database table "' . $tableName . '".');
        }
        $this->outputLine('All done');
    }

    /**
     * Fetches an array of all fields that have l10n_mode=exclude set, based on the TCA configuration
     *
     * @param $tableName
     * @return array
     */
    protected function getFieldsExcludedForTranslations($tableName)
    {
        $fieldNames = [];
        foreach ($GLOBALS['TCA'][$tableName]['columns'] as $fieldName => $columnConfiguration) {
            if (isset($columnConfiguration['l10n_mode']) && $columnConfiguration['l10n_mode'] === 'exclude') {
                $fieldNames[] = $fieldName;
            }
        }
        return $fieldNames;
    }

    /**
     * Fetches the current database connection
     *
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
