<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "l10nexclude".
 *
 * Auto generated 16-10-2016 08:46
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'l10n_mode exclude',
    'description' => 'Fills all fields marked as "l10n_mode=exclude" within a translations with the original translation record value.',
    'category' => 'misc',
    'author' => 'CMS Experts',
    'author_email' => 'benni@typo3.org',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-8.9.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
