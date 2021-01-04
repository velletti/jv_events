<?php
declare(strict_types = 1);
namespace JVE\JvEvents\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class JvEventsCustomLayoutElement extends AbstractFormElement
{
    public function render()
    {
        $result = $this->initializeResultArray();

        $allSettings = $this->getSettings() ;
        $settings = $allSettings['plugin.']['tx_jvevents_events.']['settings.'];

        $PA = $this->data['parameterArray']  ;
        $layoutType = $this->data['parameterArray']['fieldConf']['config']['parameters']['layoutType'] ;


        if ( $layoutType == '' ) {
            $result['html'] =  '<div class="alert alert-error">Typo Script of this Extension : settings.list.layouts.layout1 .. not set ! </div>' ;
        } else {
            $layouts = $settings[$layoutType]['layouts.'] ;
            if ( !is_array($layouts)) {
                $result['html'] = '<div class="alert alert-error">Typo Script of this Extension : settings.list.layouts.layout1 .. not an Array ! </div>' ;
            } else {
                $result['html'] =  '<select name="' . $PA['itemFormElName'] . '"';
                $result['html'] .= ' onchange="' . htmlspecialchars(implode('', $PA['fieldChangeFunc'])) . '"';
                $result['html'] .= $PA['onFocus'];
                $result['html']  .= ' >';
                foreach ( $layouts as $key => $layout) {
                    $selected = '' ;
                    if ( $key == htmlspecialchars($PA['itemFormElValue']) ) {
                        $selected = ' selected="selected"' ;
                    }
                    $result['html'] .= '<option ' . $selected . ' value="' . $key .  '"> ' . $layout . '</option>';
                }
                $result['html']  .= '</select>';
            }
        }



        return $result;
    }

    public function getSettings() {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface');
        $settings = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT );

        return $settings ;
    }
}
