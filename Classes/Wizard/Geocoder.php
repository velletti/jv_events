<?php
namespace JVelletti\JvEvents\Wizard;

use TYPO3\CMS\Backend\Controller\Wizard\AbstractWizardController;
use JVelletti\JvEvents\Utility\GeocoderUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
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
 * @package JVelletti\JvEvents\Wizard
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
		$this->getLanguageService()->includeLLFile('EXT:jv_events/Resources/Private/Language/locallang_be.xlf');
		$this->initialize();
	}

	/**
	 * Some initializing
	 */
	protected function initialize(){

        $this->geoCoder = GeneralUtility::makeInstance(GeocoderUtility::class) ;

		$this->docTemplate = GeneralUtility::makeInstance(DocumentTemplate::class);

		$this->docTemplate->JScode = $this->geoCoder ->javascriptCode ;
		GeneralUtility::makeInstance(PageRenderer::class)->addCssFile('EXT:jv_events/Resources/Public/Css/geocoder.css', 'stylesheet', 'screen', '');

	}

	/**
	 * Gets address data from DB (by GET params P)
	 * @return array
	 */
	protected function getAddressDataFromDb(){

		// Parameters
		$parameters = $GLOBALS['TYPO3_REQUEST']->getParsedBody()['P'] ?? $GLOBALS['TYPO3_REQUEST']->getQueryParams()['P'] ?? null;

		// print_r($parameters);

		$addressData = BackendUtility::getRecord($parameters['table'], $parameters['uid']);

		// If it is a new, unsaved record, we get the following parameter for uid:
		// e.g.: [uid] => NEW57f654657625f780378440
		// So set at least the uid, so we can read the fields inside the parent window
		if(empty($addressData) && preg_match('/^NEW/', (string) $parameters['uid'])){

			unset($addressData);
			$addressData = ['uid' => $parameters['uid']];

		}

		return $addressData;

	}


	/**
  * Injects the request object for the current request or subrequest
  * Calles by Configuration/Backend/Routes.php
  * @return ResponseInterface
  */
 public function mainAction(ServerRequestInterface $request, ResponseInterface $response){


		$this->contentString = '';
		$this->contentString .= $this->docTemplate->startPage($this->getLanguageService()->sL('geocoding.page.title'));
		$this->contentString .= $this->docTemplate->header($this->getLanguageService()->sL('geocoding.page.headline'));
        $addressData = $this->getAddressDataFromDb();
        $this->contentString .= $this->geoCoder->main(false , $addressData['uid'] , "TYPO3.jQuery" );
		$this->contentString .= $this->docTemplate->endPage();
		$response->getBody()->write($this->contentString);
		return $response;

	}

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return GeneralUtility::makeInstance(LanguageServiceFactory::class)->create("default") ;
    }

}
