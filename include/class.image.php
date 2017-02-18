<?php

/**
 * 图像操作类库
 * @category   ORG
 * @package  ORG
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @update  Daniel QQ 614370790
 * 支持png的水印
 */
class Image {

    /**
     * 取得图像信息
     * @static
     * @access public
     * @param string $image 图像文件名
     * @return mixed(Array/false)
     */

    static function getImageInfo($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $imageSize = filesize($img);
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "size" => $imageSize,
                "mime" => $imageInfo['mime']
            );
            return $info;
        } else {
            return false;
        }
    }
	
	/**
     * 生成缩略图(原始比例缩放)
     * @static
     * @access public
     * @param string $image  原图
     * @param string $thumbname 缩略图文件名
	 * @param string $type 图像格式
     * @param string $maxWidth  宽度(>=0)
     * @param string $maxHeight  高度(>=0)
	 * @param boolean $allow_enlarge 是否允许放大(当缩略比例>1时,如果允许放大,则缩略图会按照缩略比对原图进行放大)
     * @param string $position 缩略图保存目录
     * @param boolean $interlace 启用隔行扫描
     * @return Mixed(Srting/false) false:失败,String:成功,缩略图路径
     */
    static function thumb($image, $thumbname, $type = '', $maxWidth = 200, $maxHeight = 50, $allow_enlarge = false, $interlace = true) {
		$maxWidth = $maxWidth < 0 ? 0 : $maxWidth;
		$maxHeight = $maxHeight < 0 ? 0 : $maxHeight;
		// 获取原图信息
        $info = Image::getImageInfo($image);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
			$allow_enlarge = $allow_enlarge ? true : false;
            $interlace = $interlace ? 1 : 0;
            unset($info);
            if($maxWidth <= 0 && $maxHeight <= 0) {
				return false;
			} elseif ($maxWidth == 0){
				$scale = $maxHeight / $srcHeight; // 计算缩放比例
			} elseif($maxHeight == 0) {
				$scale = $maxWidth / $srcWidth; // 计算缩放比例
			} else {
				$scale = min($maxWidth / $srcWidth, $maxHeight / $srcHeight); // 计算缩放比例
			}
			
            if ($scale > 1 && !$allow_enlarge) {
				// 超过原图大小不再缩略
				$width = $srcWidth;
				$height = $srcHeight;
            } else {
                // 缩略图尺寸
                $width = (int) ($srcWidth * $scale);
                $height = (int) ($srcHeight * $scale);
            }

            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            if(!function_exists($createFun)) {
                return false;
            }
            $srcImg = $createFun($image);

            //创建缩略图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $thumbImg = imagecreatetruecolor($width, $height);
            else
                $thumbImg = imagecreate($width, $height);
              //png和gif的透明处理 by luofei614
            if('png'==$type){
                imagealphablending($thumbImg, false);//取消默认的混色模式（为解决阴影为绿色的问题）
                imagesavealpha($thumbImg,true);//设定保存完整的 alpha 通道信息（为解决阴影为绿色的问题）
            }elseif('gif'==$type){
                $trnprt_indx = imagecolortransparent($srcImg);
                 if ($trnprt_indx >= 0) {
                        //its transparent
                       $trnprt_color = imagecolorsforindex($srcImg , $trnprt_indx);
                       $trnprt_indx = imagecolorallocate($thumbImg, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                       imagefill($thumbImg, 0, 0, $trnprt_indx);
                       imagecolortransparent($thumbImg, $trnprt_indx);
              }
            }
            // 复制图片
            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);
            else
                imagecopyresized($thumbImg, $srcImg, 0, 0, 0, 0, $width, $height, $srcWidth, $srcHeight);

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type)
                imageinterlace($thumbImg, $interlace);

            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            $imageFun($thumbImg, $thumbname);
            imagedestroy($thumbImg);
            imagedestroy($srcImg);
            return $thumbname;
        }
        return false;
    }
	
	/**
     * 生成裁切图(原图按照所选区域先裁切后缩放)
     * @static
     * @access public
     * @param string $image  原图
     * @param string $destname 缩略图文件名
	 * @param string $type 图像格式
	 * @param integer $srcLeft 原图裁切区域的左顶点x坐标(原图的左顶点为坐标原点)
     * @param integer $srcTop 原图裁切区域的左顶点y坐标(原图的左顶点为坐标原点)
	 * @param string $cutWidth 裁切区域宽度(>0)
     * @param string $cutHeight 裁切区域高度(>0)
	 * @param boolean $destWidth 目标图像宽度(目标宽高与裁切区域宽高不一致时会缩放)
     * @param string $destHeight 目标图像高度(目标宽高与裁切区域宽高不一致时会缩放)
     * @param boolean $interlace 启用隔行扫描
     * @return Mixed(Srting/false)  false:失败,String:成功,裁切后图像路径
     */
	static function cut($image, $destname, $type = '', $srcLeft = 0, $srcTop = 0, $cutWidth = 200, $cutHeight = 200, $destWidth = 200, $destHeight = 200, $interlace = true) {
		if($destWidth <= 0 || $destHeight <= 0) {
			return false;
		}
		// 获取原图信息
        $info = Image::getImageInfo($image);
        if ($info !== false) {
            $srcWidth = $info['width'];
            $srcHeight = $info['height'];
            $type = empty($type) ? $info['type'] : $type;
            $type = strtolower($type);
			$allow_enlarge = $allow_enlarge ? true : false;
            $interlace = $interlace ? 1 : 0;
            unset($info);
			
			if($srcLeft < 0 || $srcLeft > $srcWidth) {
				$srcLeft = 0;
			}
			if($srcTop < 0 || $srcTop > $srcHeight) {
				$srcTop = 0;
			}
			if($cutWidth > $srcWidth || $cutWidth <= 0) {
				$cutWidth = $srcWidth;
			}
			if($cutHeight > $srcHeight || $cutHeight <= 0) {
				$cutHeight = $srcHeight;
			}
			
            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            if(!function_exists($createFun)) {
                return false;
            }
            $srcImg = $createFun($image);

            //创建目标图
            if ($type != 'gif' && function_exists('imagecreatetruecolor'))
                $destImg = imagecreatetruecolor($destWidth, $destHeight);
            else
                $destImg = imagecreate($destWidth, $destHeight);
              //png和gif的透明处理 by luofei614
            if('png'==$type){
                imagealphablending($destImg, false);//取消默认的混色模式（为解决阴影为绿色的问题）
                imagesavealpha($destImg,true);//设定保存完整的 alpha 通道信息（为解决阴影为绿色的问题）
            }elseif('gif'==$type){
                $trnprt_indx = imagecolortransparent($srcImg);
                 if ($trnprt_indx >= 0) {
                        //its transparent
                       $trnprt_color = imagecolorsforindex($srcImg , $trnprt_indx);
                       $trnprt_indx = imagecolorallocate($destImg, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                       imagefill($destImg, 0, 0, $trnprt_indx);
                       imagecolortransparent($destImg, $trnprt_indx);
              }
            }
            // 复制图片
            if (function_exists("ImageCopyResampled"))
                imagecopyresampled($destImg, $srcImg, 0, 0, $srcLeft, $srcTop, $destWidth, $destHeight, $cutWidth, $cutHeight);
            else
                imagecopyresized($destImg, $srcImg, 0, 0, $srcLeft, $srcTop, $destWidth, $destHeight, $cutWidth, $cutHeight);

            // 对jpeg图形设置隔行扫描
            if ('jpg' == $type || 'jpeg' == $type)
                imageinterlace($destImg, $interlace);

            // 生成图片
            $imageFun = 'image' . ($type == 'jpg' ? 'jpeg' : $type);
            $imageFun($destImg, $destname);
            imagedestroy($destImg);
            imagedestroy($srcImg);
            return $destname;
        }
        return false;
	}
	
    /**
     * 为图片添加水印
     * @static public
     * @param string $source 原文件名
     * @param string $water  水印图片
     * @param string $$savename  添加水印后的图片名
     * @param string $alpha  水印的透明度
     * @return void
     */
    static public function water($source, $water, $savename=null, $alpha=80) {
        //检查文件是否存在
        if (!file_exists($source) || !file_exists($water))
            return false;

        //图片信息
        $sInfo = self::getImageInfo($source);
        $wInfo = self::getImageInfo($water);

        //如果图片小于水印图片，不生成图片
        if ($sInfo["width"] < $wInfo["width"] || $sInfo['height'] < $wInfo['height'])
            return false;

        //建立图像
        $sCreateFun = "imagecreatefrom" . $sInfo['type'];
        $sImage = $sCreateFun($source);
        $wCreateFun = "imagecreatefrom" . $wInfo['type'];
        $wImage = $wCreateFun($water);

        //设定图像的混色模式
        imagealphablending($wImage, true);

        //图像位置,默认为右下角右对齐
        $posY = $sInfo["height"] - $wInfo["height"];
        $posX = $sInfo["width"] - $wInfo["width"];

        //生成混合图像
        self::imagecopymerge_alpha($sImage, $wImage, $posX, $posY, 0, 0, $wInfo['width'], $wInfo['height'], $alpha);

        //输出图像
        $ImageFun = 'Image' . $sInfo['type'];
        //如果没有给出保存文件名，默认为原图像名
        if (!$savename) {
            $savename = $source;
            @unlink($source);
        }
        //保存图像
        $ImageFun($sImage, $savename);
        imagedestroy($sImage);
    }

    function showImg($imgFile, $text='', $x='10', $y='10', $alpha='50') {
        //获取图像文件信息
        //2007/6/26 增加图片水印输出，$text为图片的完整路径即可
        $info = Image::getImageInfo($imgFile);
        if ($info !== false) {
            $createFun = str_replace('/', 'createfrom', $info['mime']);
            $im = $createFun($imgFile);
            if ($im) {
                $ImageFun = str_replace('/', '', $info['mime']);
                //水印开始
                if (!empty($text)) {
                    $tc = imagecolorallocate($im, 0, 0, 0);
                    if (is_file($text) && file_exists($text)) {//判断$text是否是图片路径
                        // 取得水印信息
                        $textInfo = Image::getImageInfo($text);
                        $createFun2 = str_replace('/', 'createfrom', $textInfo['mime']);
                        $waterMark = $createFun2($text);
                        //$waterMark=imagecolorallocatealpha($text,255,255,0,50);
                        $imgW = $info["width"];
                        $imgH = $info["width"] * $textInfo["height"] / $textInfo["width"];
                        //$y    =   ($info["height"]-$textInfo["height"])/2;
                        //设置水印的显示位置和透明度支持各种图片格式
                        imagecopymerge($im, $waterMark, $x, $y, 0, 0, $textInfo['width'], $textInfo['height'], $alpha);
                    } else {
                        imagestring($im, 80, $x, $y, $text, $tc);
                    }
                    //ImageDestroy($tc);
                }
                //水印结束
                if ($info['type'] == 'png' || $info['type'] == 'gif') {
                    imagealphablending($im, FALSE); //取消默认的混色模式
                    imagesavealpha($im, TRUE); //设定保存完整的 alpha 通道信息
                }
                Header("Content-type: " . $info['mime']);
                $ImageFun($im);
                @ImageDestroy($im);
                return;
            }

            //保存图像
            $ImageFun($sImage, $savename);
            imagedestroy($sImage);
            //获取或者创建图像文件失败则生成空白PNG图片
            $im = imagecreatetruecolor(80, 30);
            $bgc = imagecolorallocate($im, 255, 255, 255);
            $tc = imagecolorallocate($im, 0, 0, 0);
            imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
            imagestring($im, 4, 5, 5, "no pic", $tc);
            Image::output($im);
            return;
        }
    }

    /**
     * 把图像转换成字符显示
     * @static
     * @access public
     * @param string $image  要显示的图像
     * @param string $type  图像类型，默认自动获取
     * @return string
     */
    static function showASCIIImg($image, $string='', $type='') {
        $info = Image::getImageInfo($image);
        if ($info !== false) {
            $type = empty($type) ? $info['type'] : $type;
            unset($info);
            // 载入原图
            $createFun = 'ImageCreateFrom' . ($type == 'jpg' ? 'jpeg' : $type);
            $im = $createFun($image);
            $dx = imagesx($im);
            $dy = imagesy($im);
            $i = 0;
            $out = '<span style="padding:0px;margin:0;line-height:100%;font-size:1px;">';
            set_time_limit(0);
            for ($y = 0; $y < $dy; $y++) {
                for ($x = 0; $x < $dx; $x++) {
                    $col = imagecolorat($im, $x, $y);
                    $rgb = imagecolorsforindex($im, $col);
                    $str = empty($string) ? '*' : $string[$i++];
                    $out .= sprintf('<span style="margin:0px;color:#%02x%02x%02x">' . $str . '</span>', $rgb['red'], $rgb['green'], $rgb['blue']);
                }
                $out .= "<br>\n";
            }
            $out .= '</span>';
            imagedestroy($im);
            return $out;
        }
        return false;
    }

    /**
     * 生成UPC-A条形码
     * @static
     * @param string $type 图像格式
     * @param string $type 图像格式
     * @param string $lw  单元宽度
     * @param string $hi   条码高度
     * @return string
     */
    static function UPCA($code, $type='png', $lw=2, $hi=100) {
        static $Lencode = array('0001101', '0011001', '0010011', '0111101', '0100011',
    '0110001', '0101111', '0111011', '0110111', '0001011');
        static $Rencode = array('1110010', '1100110', '1101100', '1000010', '1011100',
    '1001110', '1010000', '1000100', '1001000', '1110100');
        $ends = '101';
        $center = '01010';
        /* UPC-A Must be 11 digits, we compute the checksum. */
        if (strlen($code) != 11) {
            die("UPC-A Must be 11 digits.");
        }
        /* Compute the EAN-13 Checksum digit */
        $ncode = '0' . $code;
        $even = 0;
        $odd = 0;
        for ($x = 0; $x < 12; $x++) {
            if ($x % 2) {
                $odd += $ncode[$x];
            } else {
                $even += $ncode[$x];
            }
        }
        $code.= ( 10 - (($odd * 3 + $even) % 10)) % 10;
        /* Create the bar encoding using a binary string */
        $bars = $ends;
        $bars.=$Lencode[$code[0]];
        for ($x = 1; $x < 6; $x++) {
            $bars.=$Lencode[$code[$x]];
        }
        $bars.=$center;
        for ($x = 6; $x < 12; $x++) {
            $bars.=$Rencode[$code[$x]];
        }
        $bars.=$ends;
        /* Generate the Barcode Image */
        if ($type != 'gif' && function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor($lw * 95 + 30, $hi + 30);
        } else {
            $im = imagecreate($lw * 95 + 30, $hi + 30);
        }
        $fg = ImageColorAllocate($im, 0, 0, 0);
        $bg = ImageColorAllocate($im, 255, 255, 255);
        ImageFilledRectangle($im, 0, 0, $lw * 95 + 30, $hi + 30, $bg);
        $shift = 10;
        for ($x = 0; $x < strlen($bars); $x++) {
            if (($x < 10) || ($x >= 45 && $x < 50) || ($x >= 85)) {
                $sh = 10;
            } else {
                $sh = 0;
            }
            if ($bars[$x] == '1') {
                $color = $fg;
            } else {
                $color = $bg;
            }
            ImageFilledRectangle($im, ($x * $lw) + 15, 5, ($x + 1) * $lw + 14, $hi + 5 + $sh, $color);
        }
        /* Add the Human Readable Label */
        ImageString($im, 4, 5, $hi - 5, $code[0], $fg);
        for ($x = 0; $x < 5; $x++) {
            ImageString($im, 5, $lw * (13 + $x * 6) + 15, $hi + 5, $code[$x + 1], $fg);
            ImageString($im, 5, $lw * (53 + $x * 6) + 15, $hi + 5, $code[$x + 6], $fg);
        }
        ImageString($im, 4, $lw * 95 + 17, $hi - 5, $code[11], $fg);
        /* Output the Header and Content. */
        Image::output($im, $type);
    }

    static function output($im, $type='png', $filename='') {
        header("Content-type: image/" . $type);
        $ImageFun = 'image' . $type;
        if (empty($filename)) {
            $ImageFun($im);
        } else {
            $ImageFun($im, $filename);
        }
        imagedestroy($im);
    }

     static function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $opacity=$pct;
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);

        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
        //$opacity = 100 - $opacity;

        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
    }

}