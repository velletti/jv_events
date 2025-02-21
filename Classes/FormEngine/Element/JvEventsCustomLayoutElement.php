<?php
declare(strict_types = 1);
namespace JVelletti\JvEvents\FormEngine\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class JvEventsCustomLayoutElement extends AbstractFormElement
{

    /**
     * Main data array to work on, given from parent to child elements
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Container objects give $nodeFactory down to other containers.
     */
    public function __construct(NodeFactory $nodeFactory, array $data  = [])
    {
        parent::__construct($nodeFactory, $data);
    }

    public function render(): array
    {
        $result = $this->initializeResultArray();

        $pid = (int)$this->data['databaseRow']['pid'] ;
        $lng = (int)$this->data['databaseRow']['sys_language_uid'] ;
        if( $lng < 1) {
            $lng = 0 ;
        }
        $path = \JVelletti\JvTyposcript\Utility\TyposcriptUtility::getPath($pid , $lng , "tx_jvevents_events") ;
        $typoScript = \JVelletti\JvTyposcript\Utility\TyposcriptUtility::loadTypoScriptviaCurl($path) ;

        $settings =  $typoScript['settings'] ?? [] ;

        $PA = $this->data['parameterArray']  ;
        $layoutType = ( $this->data['parameterArray']['fieldConf']['config']['parameters']['layoutType'] ?? "list." ) ;
        $layoutType = rtrim( $layoutType , "." ) ;

        $layouts = ($settings[$layoutType]['layouts'] ?? false ) ;
        if ( !is_array($layouts)) {
            $layouts = [ "1Allplan" => "1 - Allplan - List with Filters" ,
               "2Megra"   => "2 - Megra-  List with Filters and Link to first related File" ,
               "5Tango" => "5 - Tango - List with Filters " ,
            ] ;
        }
        $result['html'] =  '<select name="' . $PA['itemFormElName'] . '"';
        $result['html'] .= $PA['onFocus'];
        $result['html']  .= ' >';
        foreach ( $layouts as $key => $layout) {
            $selected = '' ;
            if ( $key == htmlspecialchars((string) $PA['itemFormElValue']) ) {
                $selected = ' selected="selected"' ;
            }
            $result['html'] .= '<option ' . $selected . ' value="' . $key .  '"> ' . $layout . '</option>';
        }
        $result['html']  .= '</select>';

        return $result;
    }

    public function getSettings() {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        return  $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT );
    }
}
