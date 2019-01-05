<?php
namespace JVE\JvEvents\Wizard;

use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Geocoder
 * See examples in: typo3/sysext/backend/Classes/Controller/Wizard/
 * @author Peter Benke <pbenke@allplan.com>
 * @package JVE\JvEvents\Wizard
 */

class Geocoder extends \TYPO3\CMS\Backend\Controller\Wizard\AbstractWizardController {

	/**
	 * Document template object
	 * @var DocumentTemplate
	 */
	public $doc;

    /**
     * @var \JVE\JvEvents\Utility\Geocoder
     */
    public $geoCoder ;

	/**
	 * @var string
	 */
	public $content;

	/**
	 * Geocoder constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->getLanguageService()->includeLLFile('EXT:jv_events/Resources/Private/Language/locallang_be.xlf');
		$this->init();
	}

	/**
	 * Some initializing
	 */
	protected function init(){

        $this->geoCoder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("JVE\\JvEvents\\Utility\\Geocoder") ;

		$this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);

		$this->doc->JScode = $this->geoCoder ->javascriptCode ;
		$this->doc->addStyleSheet('The Google Geocoder',  '../typo3conf/ext/jv_events/Resources/Public/Css/geocoder.css' );

	}

	/**
	 * Gets address data from DB (by GET params P)
	 * @return array
	 */
	protected function getAddressDataFromDb(){

		// Parameters
		$parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('P');

		// print_r($parameters);

		$addressData = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord($parameters['table'], $parameters['uid']);

		// If it is a new, unsaved record, we get the following parameter for uid:
		// e.g.: [uid] => NEW57f654657625f780378440
		// So set at least the uid, so we can read the fields inside the parent window
		if(empty($addressData) && preg_match('/^NEW/', $parameters['uid'])){

			unset($addressData);
			$addressData = array(
				'uid' => $parameters['uid']
			);

		}

		return $addressData;

	}


	/**
	 * Injects the request object for the current request or subrequest
	 * Calles by Configuration/Backend/Routes.php
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @param \Psr\Http\Message\ResponseInterface $response
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function mainAction(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response){


		$this->content = '';
		$this->content .= $this->doc->startPage($this->getLanguageService()->getLL('geocoding.page.title'));
		$this->content .= $this->doc->header($this->getLanguageService()->getLL('geocoding.page.headline'));
        $addressData = $this->getAddressDataFromDb();
        $this->content .= $this->geoCoder->main(false , $addressData['uid'] , "TYPO3.jQuery" );
		$this->content .= $this->doc->endPage();
		$response->getBody()->write($this->content);
		return $response;

	}

}
