<?php
namespace JVelletti\JvEvents\ViewHelpers;
/***************************************************************
 * Copyright notice
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
// composer require endroid/qr-code
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Endroid\QrCode\QrCode;

/**
 */

class QrcodeViewHelper extends AbstractViewHelper {

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;


    /** * Constructor *
     * @api */
    public function initializeArguments() {
        $this->registerArgument('string', 'string', 'The string that should be decoded', false , "-" );
    }

	/**
	 * Render a special sign if the field is required

	 * @return string
	 */
	public function render() {
        $qrCode = new QrCode( ( $this->arguments['string'] ));
		return base64_encode( $qrCode->writeString());
	}
}
