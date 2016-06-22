<?php
function generate($fontfile,$text=false,$size=25,$width=800, $height=200,$text_color='000000',$background_color='ffffff')
{
    $fontname = getfontname($fontfile);
    $name     = mb_convert_encoding($fontname[3]['name'], 'utf-8', 'utf-16be') . ' ' . $fontname[0]['name'];
    $text     = (!$text) ? $name : $text;
    /*
    foreach($fontname as $name) {
    if($name['language']==1033){$code='utf-16le';}
    elseif($name['language']==2052){$code='utf-16be';}
    PRINT(mb_convert_encoding($name['name'],'utf-8',$code));
    }
    */
    header("Content-type: image/png;charset=utf-8");
    //建立图片大小
    $im               = imagecreate($width, $height);
    //绘制背景
    $background_color = hex2rgb($background_color);
    $background_color = imagecolorallocate($im, $background_color['r'], $background_color['g'], $background_color['b']);
    imagefilledrectangle($im, 0, 0, ($width - 1), ($height - 1), $background_color);
    //绘制文字
    $text_color = hex2rgb($text_color);
    $text_color = imagecolorallocate($im, $text_color['r'], $text_color['g'], $text_color['b']);
    $fontBox    = imagettfbbox($size, 0, $fontfile, $text); //文字水平居中
    imagettftext($im, $size, 0, ceil(($width - $fontBox[2]) / 2), (($height + $size) / 2), $text_color, $fontfile, $text);
    //输出
    imagepng($im);
    //释放
    imagedestroy($im);
}
function hex2rgb($colour)
{
    if ($colour[0] == '#') {
        $colour = substr($colour, 1);
    }
    if (strlen($colour) == 6) {
        list($r, $g, $b) = array(
            $colour[0] . $colour[1],
            $colour[2] . $colour[3],
            $colour[4] . $colour[5]
        );
    } elseif (strlen($colour) == 3) {
        list($r, $g, $b) = array(
            $colour[0] . $colour[0],
            $colour[1] . $colour[1],
            $colour[2] . $colour[2]
        );
    } else {
        return false;
    }
    $r = hexdec($r);
    $g = hexdec($g);
    $b = hexdec($b);
    return array(
        'r' => $r,
        'g' => $g,
        'b' => $b
    );
}
function GetFontName($FilePath)
{
    $fp = fopen($FilePath, 'r');
    if ($fp) {
        //TT_OFFSET_TABLE
        $meta = unpack('n6', fread($fp, 12));
        //检查是否是一个true type字体文件以及版本号是否为1.0
        if ($meta[1] != 1 || $meta[2] != 0)
            return FALSE;
        $Found = FALSE;
        for ($i = 0; $i < $meta[3]; $i++) {
            //TT_TABLE_DIRECTORY
            $tablemeta = unpack('N4', $data = fread($fp, 16));
            if (substr($data, 0, 4) == 'name') {
                $Found = TRUE;
                break;
            }
        }
        if ($Found) {
            fseek($fp, $tablemeta[3]);
            //TT_NAME_TABLE_HEADER
            $tablecount = unpack('n3', fread($fp, 6));
            $Found      = FALSE;
            for ($i = 0; $i < $tablecount[2]; $i++) {
                //TT_NAME_RECORD
                $table = unpack('n6', fread($fp, 12));
                if ($table[4] == 1) {
                    $npos = ftell($fp);
                    fseek($fp, $n = $tablemeta[3] + $tablecount[3] + $table[6], SEEK_SET);
                    $fontname = trim($x = fread($fp, $table[5]));
                    if (strlen($fontname) > 0) {
                        $names[] = array(
                            'platform' => $table[1], //平台（操作系统）
                            'language' => $table[3], //字体名称的语言
                            'encoding' => $table[2], //字体名称的编码
                            'name' => $fontname //字体名称                       
                        );
                        //break;
                    }
                    fseek($fp, $npos, SEEK_SET);
                }
            }
        }
        fclose($fp);
    }
    return $names;
}