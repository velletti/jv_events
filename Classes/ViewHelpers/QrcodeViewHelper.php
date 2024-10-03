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
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\RoundBlockSizeMode;


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
        // Create a new instance of PngWriter
        $writer = new PngWriter();

        // Create QR code
        $qrCode = QrCode::create($this->arguments['string'])
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(96, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        // Generate the QR code and encode it in base64
        $result = $writer->write($qrCode);
		return base64_encode( (string) $result->getString());
	}
}
