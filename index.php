<?php 
//incluyo la clase
include('resize_class.php');

//init-->

$resizeObj = new Resize('https://www.cecidiomas.es/images/cecidiomases/684-thinking-of-getting-a-cat-international-cat-care-3435.png');

//resize image

$resizeObj -> resizeImage(150,100,'crop');

//save image 

$resizeObj -> saveImage('sample-resized.png',100);
?>