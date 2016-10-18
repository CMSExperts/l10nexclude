<?php
namespace CMSExperts\L10nexclude\Hooks;

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
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Hook class for the data handler
 */
class DataHandlerTranslationFieldsUpdater
{

    /**
     * Hook to update all translations of a record when the original language is updated
     *
     * @param string $status either "new" or "update"
     * @param string $tableName
     * @param mixed $id
     * @param array $fieldArray
     * @param $dataHandlerObject DataHandler
     */
    public function processDatamap_postProcessFieldArray($status, $tableName, $id, &$fieldArray, $dataHandlerObject)
    {
        // check if the table is localizable
        if (!BackendUtility::isTableLocalizable($tableName)) {
            return;
        }

        // the table does not contain ANY "l10n_mode=exclude" column
        $overlaidFieldNames = $this->getFieldsExcludedForTranslations($tableName);
        if (empty($overlaidFieldNames)) {
            return;
        }

        $languageFieldName = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'];
        $originalPointerFieldName = $GLOBALS['TCA'][$tableName]['ctrl']['transOrigPointerField'];

        // Use case 1
        // Check for "new translations", by fetching the data from the original record
        if ($status === 'new') {
            if (!isset($fieldArray[$languageFieldName]) || (int)$fieldArray[$languageFieldName] === 0) {
                return;
            }

            // check what is in "l10n_parent"
            $originalRecordUid = $fieldArray[$originalPointerFieldName];
            $fullRecord = BackendUtility::getRecord($tableName, $originalRecordUid);

            // update the field array and enrich the data (given by reference) of the original language
            foreach ($overlaidFieldNames as $fieldName) {
                $fieldArray[$fieldName] = $fullRecord[$fieldName];
            }
        }

        // Use case 2
        // Update all associated translations if the base record is going to be updated
        if ($status === 'update') {
            // check if we're working on an "original language" update, if not, no need to do something
            $fullRecord = BackendUtility::getRecord($tableName, $id);

            // somebody is updating a translation, so nothing needs to be updated.
            if ((int)$fullRecord[$languageFieldName] > 0) {
                return;
            }

            // original record, distribute all changed values to the translations
            $updateDataForTranslations = [];
            $overlaidFieldNames = array_flip($overlaidFieldNames);
            foreach ($fieldArray as $fieldName => $value) {
                if (isset($overlaidFieldNames[$fieldName])) {
                    $updateDataForTranslations[$fieldName] = $value;
                }
            }

            // update all translations
            if (!empty($updateDataForTranslations)) {
                $this->getDatabaseConnection()->exec_UPDATEquery(
                    $tableName,
                    $originalPointerFieldName . '=' . (int)$id,
                    $updateDataForTranslations
                );
            }
        }
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
