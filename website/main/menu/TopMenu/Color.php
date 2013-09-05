<?php
define('COLOR_M',  0);
define('COLOR_W',  1);
define('COLOR_L',  2);
define('COLOR_LW', 3);
define('COLOR_WL', 4);
function tColor($mode, $content){
  if(in_array($mode, array(COLOR_M, COLOR_W, COLOR_L))){
    $color   = '';
    if($mode == COLOR_M) $color = ' color-map';
    if($mode == COLOR_W) $color = ' color-word';
    if($mode == COLOR_L) $color = ' color-language';
    $content = "<div class='inline$color'>$content</div>";
  }else if(in_array($mode, array(COLOR_LW, COLOR_WL))){
    if($mode == COLOR_LW){ $c1 = 'color-language'; $c2 = 'color-word';}
    if($mode == COLOR_WL){ $c1 = 'color-word'; $c2 = 'color-language';}
    preg_match('/^(.*) [Xx×] (.*)$/u', $content, $matches);
    if(count($matches) > 2){
      $m1 = $matches[1];
      $m2 = $matches[2];
      $content = "<div class='inline $c1'>$m1</div>×<div class='inline $c2'>$m2</div>";
    }
  }
  return $content;
}
?>
