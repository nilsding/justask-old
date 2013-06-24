<?php
  function fixDir(){
    $scrpt=$_SERVER['SCRIPT_NAME'];
    if(stristr($scrpt,"/")){
      $arSc=explode("/", $scrpt);
      $scNumb=count($arSc);

      for($i=0; $i<$scNumb-1; $i++){
        $newDir.=$arSc[$i];
	if($i<($scNumb-2)) $newDir.="/";
      }

      return $newDir;
    }
    return "";
  }
