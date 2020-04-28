<?php 
	Class Resize {

		//class variables

		private $image;
		private $width;
		private $height;
		private $imageResized;

		public function __construct($fileName) {

			//abrimos el archivo (la función openImage está más adelante en la clase)

			$this->image = $this->openImage($fileName);

			//obtener altura y ancho

			$this->width = imagesx($this->image);
			$this->height = imagesy($this->image);
			//estos dos métodos imagesx imagesy son funciones incorporadas de la librería GD. Ellas obtienen respectiv. altura y anchura de tu imagen
		}

		private function openImage($file){

			//obtengo la exten´sión. el método strrchr() obtiene, en el string, el último needle indicado en los parámetros

			$ext = strtolower(strrchr($file, '.'));

			switch($ext) {

				case '.jpg':
				case '.jpeg':
					$img = @imagecreatefromjpeg($file);
				break;
				case '.gif':
					$img = @imagecreatefromgif($file);
				break;
				case '.png':
					$img = @imagecreatefrompng($file);
				break;
				default:
					$img = false;
				break;
			}

			return $img;
		}

		public function resizeImage($newWidth,$newHeight,$option="auto"){

			//obtener tamaños optimales dependiendo del parámetro opcional $option

			$optionArray = $this->getDimensions($newWidth,$newHeight,strtolower($option));

			$optimalWidth = $optionArray['optimalWidth'];
			$optimalHeight = $optionArray['optimalHeight'];

			//creo un canvas image de x,y tamaño

			$this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
			imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

			//si la opción es "crop", también corta

			if ($option == 'crop') {

				$this->crop($optimalWidth,$optimalHeight,$newWidth,$newHeight);
			}
		}

		private function getDimensions($newWidth,$newHeight,$option) {

			switch($option) {

				case 'exacto':
					$optimalWidth = $newWidth;
					$optimalHeight = $newHeight;
				break;

				case 'retrato':
					$optimalWidth = $this->getSizeByFixedHeight($newHeight);
					$optimalHeight = $newHeight;
				break;

				case 'paisaje':
					$optimalWidth = $newWidth;
					$optimalHeight = $this->getSizeByFixedWidth($newWidth);
				break;

				case 'auto':
					$optionArray = $this->getSizeByFixedWidth($newWidth);
					$optimalWidth = $optionArray['optimalWidth'];
					$optimalHeight = $optionArray['optimalHeight'];
				break;

				case 'crop':
					$optionArray = $this->getOptimalCrop($newWidth,$newHeight);
					$optimalWidth = $optionArray['optimalWidth'];
					$optimalHeight = $optionArray['optimalHeight'];
				break;
			}

			return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);

		}

		private function getSizeByFixedHeight($newHeight) {

			$ratio = $this->width / $this->height;
			$newWidth = $newHeight * $ratio;

			return $newWidth;
		}

		private function getSizeByFixedWidth($newWidth) {

			$ratio = $this->height / $this->width;
			$newHeight = $newWidth * $ratio;

			return $newHeight;
		}

		private function getSizeByAuto($newWidth,$newHeight){

			if($this->height < $this->width) {
				//la imagen es más amplia que alta (landscape)
				$optimalWidth = $newWidth;
				$optimalHeight = $this->getSizeByFixedWidth($newWidth);
			}elseif($this->height > $this->width){
				//la imagen es más alta que amplia (retrato)
				$optimalWidth = $this->getSizeByFixedHeight($newHeight);
				$optimalHeight = $newHeight;				
			}else{
				//la imagen es un cuadrado
				if($newHeight < $newWidth){
					$optimalWidth = $newWidth;
					$optimalHeight = $this->getSizeByFixedWidth($newWidth);
				}elseif($newHeight > $newWidth){
					$optimalWidth = $this->getSizeByFixedHeight($newHeight);
					$optimalHeight = $newHeight;
				}else{
					//redimensionar de cuadrado a cuadrado
					$optimalWidth = $newWidth;
					$optimalHeight = $newHeight;
				}
			}
			return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
		}

		private function getOptimalCrop($newWidth,$newHeight){

			$heightRatio = $this->height / $newHeight;
			$widthRatio = $this->width / $newWidth;

			if($heightRatio < $widthRatio){
				$optimalRatio = $heightRatio;
			}else{
				$optimalRatio = $widthRatio;
			}

			$optimalHeight = $this->height / $optimalRatio;

			$optimalWidth = $this->width / $optimalRatio;

			return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);

		}

		private function crop($optimalWidth,$optimalHeight,$newWidth,$newHeight) {

			//encontrar centro: utilizado para cortar

			$cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
			$cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

			$crop = $this->imageResized;
			//imageDestroy($this->imageResized);

			//cortar desde el centro hasta el tamaño pedido
			$this ->imageResized = imagecreatetruecolor($newWidth,$newHeight);
			imagecopyresampled($this->imageResized,$crop,0,0,$cropStartY,$cropStartY,$newWidth,$newHeight,$newWidth,$newHeight);
		}

		//guardar la imagen

		public function saveImage($savePath,$imageQuality="100") {
			//obtener extensión

			$extension = strrchr($savePath, '.');
			$extension = strtolower($extension);

			switch($extension) {

				case '.jpg':
				case '.jpeg':
					if(imagetypes() & IMG_JPG) {

						imagejpeg($this->imageResized, $savePath, $imageQuality);
					}
					break;
				case '.gif'	:
					if(imagetypes() & IMG_GIF) {
						imagegif($this->imageResized, $savePath);
					}
				case '.png':
					//scale quality from o to 9
					$scaleQuality = round(($imageQuality/100) * 9)	;

					//invert quality setting as 0 is best, not 9

					$invertScaleQuality = 9 - $scaleQuality;

					if(imagetypes() & IMG_PNG) {
						imagepng($this->imageResized, $savePath, $invertScaleQuality);
					}
					break;

					default:
					//si no hay extensíón, no guarda la img
					break;
			}

			imagedestroy($this->imageResized);
		}
	}
?>