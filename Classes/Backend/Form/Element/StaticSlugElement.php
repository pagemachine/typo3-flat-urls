<?php

declare(strict_types=1);

namespace Pagemachine\FlatUrls\Backend\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class StaticSlugElement extends AbstractFormElement
{
    /**
     * Default field information enabled for this element.
     *
     * @var array
     */
    protected $defaultFieldInformation = [
        'tcaDescription' => [
            'renderType' => 'tcaDescription',
        ],
    ];

    public function render(): array
    {
        $table = $this->data['tableName'];
        $row = $this->data['databaseRow'];
        $parameterArray = $this->data['parameterArray'];

        $fieldId = StringUtility::getUniqueId('formengine-input-');
        $itemValue = $parameterArray['itemFormElValue'];
        // Convert UTF-8 characters back (that is important, see Slug class when sanitizing)
        $itemValue = rawurldecode($itemValue);

        $languageId = 0;
        if (isset($GLOBALS['TCA'][$table]['ctrl']['languageField']) && !empty($GLOBALS['TCA'][$table]['ctrl']['languageField'])) {
            $languageField = $GLOBALS['TCA'][$table]['ctrl']['languageField'];
            $languageId = (int)((is_array($row[$languageField]) ? $row[$languageField][0] : $row[$languageField]) ?? 0);
        }
        $baseUrl = $this->getPrefix($this->data['site'], $languageId);

        $config = $parameterArray['fieldConf']['config'];
        $size = MathUtility::forceIntegerInRange($config['size'] ?? $this->defaultInputWidth, $this->minimumInputWidth, $this->maxInputWidth);
        $width = (int)$this->formMaxWidth($size);

        $view = GeneralUtility::makeInstance(StandaloneView::class);

        if ((new Typo3Version())->getMajorVersion() < 13) {
            $view->setTemplatePathAndFilename('EXT:flat_urls/Resources/Private/Templates/Backend/Form/Element/v12/StaticSlugElement.html');
        } else {
            $view->setTemplatePathAndFilename('EXT:flat_urls/Resources/Private/Templates/Backend/Form/Element/StaticSlugElement.html');
        }

        $view->assignMultiple([
            'fieldInformation' => $this->renderFieldInformation(),
            'baseUrl' => $baseUrl,
            'label' => $this->renderLabel($fieldId),
            'id' => $fieldId,
            'itemValue' => $itemValue,
            'width' => $width,
        ]);

        $resultArray = $this->initializeResultArray();
        $resultArray['html'] = $view->render();

        return $resultArray;
    }

    /**
     * Render the prefix for the input field.
     *
     * @param SiteInterface $site
     * @param int $requestLanguageId
     * @return string
     */
    protected function getPrefix(SiteInterface $site, int $requestLanguageId = 0): string
    {
        try {
            $language = ($requestLanguageId < 0) ? $site->getDefaultLanguage() : $site->getLanguageById($requestLanguageId);
            $base = $language->getBase();
            $baseUrl = (string)$base;
            $baseUrl = rtrim($baseUrl, '/');
            if (!empty($baseUrl) && empty($base->getScheme()) && $base->getHost() !== '') {
                $baseUrl = 'http:' . $baseUrl;
            }
        } catch (\InvalidArgumentException $e) {
            // No site / language found
            $baseUrl = '';
        }

        return $baseUrl;
    }
}
