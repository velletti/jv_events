<?php
namespace JVE\JvEvents\Wizard;

use TYPO3\CMS\Backend\Controller\Wizard\AbstractWizardController;
use JVE\JvEvents\Utility\GeocoderUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Geocoder
 * See examples in: typo3/sysext/backend/Classes/Controller/Wizard/
 * @author Peter Benke <pbenke@allplan.com>
 * @package JVE\JvEvents\Wizard
 */

class Geocoder extends AbstractWizardController {

	/**
	 * Document template object
	 * @var DocumentTemplate
	 */
	public $doc;

    /**
     * @var GeocoderUtility
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

        $this->geoCoder = GeneralUtility::makeInstance(GeocoderUtility::class) ;

		$this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);

		$this->doc->JScode = $this->geoCoder ->javascriptCode ;
		GeneralUtility::makeInstance(PageRenderer::class)->addCssFile('../typo3conf/ext/jv_events/Resources/Public/Css/geocoder.css', 'stylesheet', 'screen', '');

	}

	/**
	 * Gets address data from DB (by GET params P)
	 * @return array
	 */
	protected function getAddressDataFromDb(){

		// Parameters
		$parameters = GeneralUtility::_GP('P');

		// print_r($parameters);

		$addressData = BackendUtility::getRecord($parameters['table'], $parameters['uid']);

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
  * @param ServerRequestInterface $request
  * @param ResponseInterface $response
  * @return ResponseInterface
  */
 public function mainAction(ServerRequestInterface $request, ResponseInterface $response){


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
