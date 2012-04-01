<?php

# get LASTFM_API_KEY
include_once('config.php');

function compute_aep($user){
  $url = 'http://ws.audioscrobbler.com/2.0/?method=user.gettopartists&user='
         .urlencode($user)
         .'&api_key='.LASTFM_API_KEY
         .'&period=overall&limit=50&format=json';

  $data = json_decode(file_get_contents($url));

  $n  = count($data->topartists->artist);
  $c1 = $data->topartists->artist[0]->playcount;
  $cn = $data->topartists->artist[$n-1]->playcount;
  $s  = 0;

  for($i=0; $i<$n; $i++)
    $s += $data->topartists->artist[$i]->playcount;

  if( $s == 0 )
    return 0;

  return round(5 - 25 * ($c1 - $cn)/$s, 2);
}

function make_png($str){
  $font_size = 2;

  $width = imagefontwidth($font_size) * strlen($str);
  $height = imagefontheight($font_size);

  $img = @imagecreatetruecolor($width, $height)
          or die("no gd stream");

  $bg = imagecolorallocate($img, 0xff, 0xff, 0xff);
  imagefill($img, 0, 0, $bg);

  $fg = imagecolorallocate($img, 0x1b, 0x1b, 0x1b);
  imagestring($img, $font_size, 0, 0, $str, $fg);

  imagepng($img);
  imagedestroy($img);
}

if( isset($_GET['u']) ){
  $user = $_GET['u'];

  header("Content-Type: image/png");

  make_png(compute_aep($user));

  exit;
}

/* nginx does the cacheing in this app */


?><html>
  <head>
    <title>aep image link</title>

    <script type="text/javascript">
      function make_link(){
        var username = document.getElementById("username");
        var imgurl = "http://aep.lolwut.net/" + escape(username.value) + ".png";
        document.getElementById("textbox").value = "[url=http://blog.nullren.com/2011/09/21/created-aep-score-image-thingy][img]"+imgurl+"[/img][/url]";
        document.getElementById("aep").src = imgurl;
        document.getElementById("derp").style.display = "block";
      }
    </script>
  </head>
  <body>
    <h6>aep image link generator</h6>
    <form action="#">
      <p>last.fm username: <input type="text" id="username" /><input type="submit" value="make bbcode" onclick="make_link(); return false" /></p>
      <p><textarea id="textbox" rows="3" cols="50"></textarea></p>
      <p id="derp" style="display:none">aep image: <img id="aep" /></p>
    </form>
  </body>
</html>
