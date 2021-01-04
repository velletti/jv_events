<?php

namespace JVE\JvEvents\ViewHelpers\Be;


class LinkViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

    /**
     * @var string
     */
    protected $tagName = 'a';
    /**
     * Initialize arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments() {
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
        $this->registerTagAttribute('uid', 'integer', 'Uid of the data record' , true );
        $this->registerTagAttribute('pageId', 'integer', 'page Uid where to go back' , true );
        $this->registerTagAttribute('onlyActual', 'integer', 'actually default : Date -90 Days if checked'  , true );
        $this->registerTagAttribute('eventId', 'integer', 'id of the event if set ' , false );
        $this->registerTagAttribute('recursive', 'integer', 'if checkbox is set to search recursive ' , false );
        $this->registerTagAttribute('table', 'string', 'Name of the database table' , false , "tx_jvevents_domain_model_event" );
        $this->registerTagAttribute('returnM', 'string', 'Module name_of_backend' , false , "web_JvEventsEventmngt");
        $this->registerTagAttribute('returnModule', 'string', 'parameterArray' , false , "tx_jvevents_web_jveventseventmngt");
        $this->registerTagAttribute('returnController', 'string', 'controller name_of_backend' , true , "EventBackend");
        $this->registerTagAttribute('returnAction', 'string', 'function name of the action' , true , "list");
    }

    /**
     *
     * Renders a link to go back to edit a specific Data entry
     *
     * @return string   return the <a> tag
     *
     */

    public function render( ) {
        $uid        = $this->arguments['uid'];
        $table   = $this->arguments['table'];
        $returnM   = $this->arguments['returnM'];
        $returnModule   = $this->arguments['returnModule'];
        $class   = $this->arguments['class'];

        $returnArray = array() ;
        if( $this->arguments['pageId'] > 0 ) {
            $returnArray['id'] = $this->arguments['pageId'] ;
        } else {

            $returnArray['id'] = intval($_GET['id']) ;
        }
        $returnArray[$returnModule] = array() ;
        $returnArray[$returnModule]['action'] =$this->arguments['returnAction'] ;
        $returnArray[$returnModule]['controller'] = $this->arguments['returnController'] ;
        $returnArray[$returnModule]['event'] =  $this->arguments['eventId'] ;
        $returnArray[$returnModule]['recursive'] = $this->arguments['recursive'] ;
        $returnArray[$returnModule]['onlyActual'] = $this->arguments['onlyActual'] ;

        /* outdated. will be removed when LTS 8 support is dropped  */
        if( $_GET['M']) {
            $returnM = $_GET['M'] ;
        }
        if( $_GET['route']) {
            $returnM = $_GET['route'] ;
        }
        $returnUrl = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl( $returnM , array( $returnArray ) ) ;
        /* *** not needed in LTS 8 - in LTS 9 strange effekt .. remove it when using URI Builder and LTS 8 support is dropped***/

        $returnUrl = str_replace( "0%5Bid%5D=" , "id=" , $returnUrl ) ;
           // tx_fetool_tools_fetoolfeuserlist%5Baction%5D=listbyclass&tx_fetool_tools_fetoolfeuserlist%5Bcontroller%5D=Feuserlist
        // tx_jvevents_web_jveventseventmngt[action]=list&tx_jvevents_web_jveventseventmngt[controller]=EventBackend
        $uri = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('record_edit', array( 'edit['. $table . '][' . $uid . ']' => 'edit' ,'returnUrl' => $returnUrl )) ;

        $this->tag->setTagName("a") ;

        $this->tag->addAttribute('href', $uri  );
        $this->tag->addAttribute('class', $class  );
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(TRUE);
        return $this->tag->render();

    }

}