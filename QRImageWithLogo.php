<?php
/**
 * Class QRImageWithLogo
 *
 * @filesource   QRImageWithLogo.php
 * @created      18.11.2020
 * @package      chillerlan\QRCodeExamples
 * @author       smiley <smiley@chillerlan.net>
 * @copyright    2020 smiley
 * @license      MIT
 *
 * @noinspection PhpComposerExtensionStubsInspection
 */

// namespace chillerlan\QRCode;

use chillerlan\QRCode\Output\{QRCodeOutputException, QRImage};

/**
 * @property \chillerlan\QRCodeExamples\LogoOptions $options
 */
class QRImageWithLogo extends QRImage{

	/**
	 * @param string|null $file
	 * @param string|null $logo
	 *
	 * @return string
	 * @throws \chillerlan\QRCode\Output\QRCodeOutputException
	 */
	public function dump(string $file = null, string $logo = null):string{
		// set returnResource to true to skip further processing for now
		$this->options->returnResource = true;

		if($logo === null || !is_file($logo) || !is_readable($logo)){
			throw new QRCodeOutputException('invalid logo');
		}

		$this->matrix->setLogoSpace(
			$this->options->logoSpaceWidth,
			$this->options->logoSpaceHeight
			// not utilizing the position here
		);

		// there's no need to save the result of dump() into $this->image here
		parent::dump($file);

		$lw = max(1, ($this->options->logoSpaceWidth - 2) * $this->options->scale);
		$lh = max(1, ($this->options->logoSpaceHeight - 2) * $this->options->scale);
		$ql = $this->matrix->size() * $this->options->scale;

		$im = imagecreatefrompng($logo);

		if($im === false){
			throw new QRCodeOutputException('unable to create logo image resource');
		}

		// get logo image size
		$w = imagesx($im);
		$h = imagesy($im);

		// scale the logo and copy it over. done!
		imagecopyresampled($this->image, $im, ($ql - $lw) / 2, ($ql - $lh) / 2, 0, 0, $lw, $lh, $w, $h);
		imagedestroy($im);

		$imageData = $this->dumpImage();

		if($file !== null){
			$this->saveToFile($imageData, $file);
		}

		if($this->options->imageBase64){
			$imageData = 'data:image/'.$this->options->outputType.';base64,'.base64_encode($imageData);
		}

		return $imageData;
	}

}
