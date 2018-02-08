<?php
if (!defined("TYPO3_MODE")) {
    die ("Access denied.");
}
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('options.saveDocNew.tx_pbsurvey_item=1');

## Extending TypoScript from static template uid=43 to set up userdefined tag:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY, "editorcfg",
    "tt_content.CSS_editor.ch.tx_pbsurvey_pi1 = < plugin.tx_pbsurvey_pi1.CSS_editor", 43);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY, "pi1/class.tx_pbsurvey_pi1.php", "_pi1",
    "list_type", 0);

if (TYPO3_MODE === 'BE') {
    $icons = [
        '0' => 'icon_tx_pbsurvey_item.gif',
        '1' => 'icon_pbsurvey_item_1.gif',
        '2' => 'icon_pbsurvey_item_2.gif',
        '3' => 'icon_pbsurvey_item_3.gif',
        '4' => 'icon_pbsurvey_item_4.gif',
        '5' => 'icon_pbsurvey_item_5.gif',
        '6' => 'icon_pbsurvey_item_6.gif',
        '7' => 'icon_pbsurvey_item_7.gif',
        '8' => 'icon_pbsurvey_item_8.gif',
        '9' => 'icon_pbsurvey_item_9.gif',
        '10' => 'icon_pbsurvey_item_10.gif',
        '11' => 'icon_pbsurvey_item_11.gif',
        '12' => 'icon_pbsurvey_item_12.gif',
        '13' => 'icon_pbsurvey_item_13.gif',
        '14' => 'icon_pbsurvey_item_14.gif',
        '15' => 'icon_pbsurvey_item_15.gif',
        '16' => 'icon_pbsurvey_item_16.gif',
        '17' => 'icon_pbsurvey_item_17.gif',
        '18' => 'icon_pbsurvey_item_18.gif',
        '19' => 'icon_pbsurvey_item_19.gif',
        '20' => 'icon_pbsurvey_item_20.gif',
        '21' => 'icon_pbsurvey_item_21.gif',
        '22' => 'icon_pbsurvey_item_22.gif',
        '23' => 'icon_pbsurvey_item_23.gif',
        '24' => 'icon_pbsurvey_item_24.gif',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($icons as $identifier => $path) {
        $iconRegistry->registerIcon($identifier, \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => 'EXT:pbsurvey/Resources/Public/Icons/' . $path]);
    }
}
?>