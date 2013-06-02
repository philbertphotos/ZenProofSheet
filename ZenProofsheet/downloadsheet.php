<?php 
ini_set( "display_errors", "1" );
error_reporting( E_ALL );

$dir        = str_replace( '\\', '/', realpath( dirname( __FILE__ ) ) );
define( 'SERVERPATH', str_replace( '/plugins/ZenProofsheet', '', $dir ) );
require_once( SERVERPATH . '/zp-core/functions.php' );
include_once( SERVERPATH . '/zp-core/template-functions.php' );
require_once( SERVERPATH . '/zp-core/lib-auth.php' );

require_once(SERVERPATH.'/plugins/ZenProofsheet/fpdf/fpdf.php');

global $_zp_current_album, $_zp_current_image;
$albumobject = getItemByID( "albums", $_GET['albumobject'] );


class proofsheet_PDF extends FPDF {

  /**
   * Print text (header, footer, or caption) with link, formatted as in cfg.
   * It converts UTF-8 back to CP1252, which is used by FPDF.
   * This will trim formatted text to fit and add ellipsis if needed.
   */
  function printText($text, $font, $x, $y, $w, $h, $link = null) {
    $ellipsis = '…'; // ASCII character 133
    // Convert from UTF-8 back to CP1252
    $text = iconv('utf-8','cp1252',$text);
    // Set color, font, and position
    $this->SetTextColor($font['r'],$font['g'],$font['b']);
    $this->SetFont($font['name'],$font['style'],$font['size']);
    $this->SetXY($x, $y);
    // Trim text if needed
    if (($this->GetStringWidth($text)) > $w) {
      // Keep trimming until the size, with ellipsis, is small enough
      while (($this->GetStringWidth($text.$ellipsis)) > $w) {
        $text = substr($text,0,strlen($text)-1);
      }
      // Add the ellipsis to the shortened text
      $text = $text.$ellipsis;
    }
    // Create text cell
    $this->Cell($w,$h,$text,0,0,$font['posn'],false,$link);
  }
      
  /**
   * Print image.  This is basically a wrapper around the FPDF image function,
   * except that it determines the file type independent of the file extension
   * and automatically resizes to main aspect ratio within the defined space.
   * Note that this provides robustness for images with incorrect filenames, such
   * as missing_movie.png being called a jpg when copied as a thumbnail in v3.0.2.
   */
  function printImage($imagePath, $x, $y, $w, $h, $link = null) {
    $imageInfo = getimagesize($imagePath); // [0]=w, [1]=h, [2]=type (1=GIF, 2=JPG, 3=PNG)
    // Figure out the filetype
    switch($imageInfo[2]) {
      case 3:
        $imageType = 'PNG';
        break;
      case 2:
        $imageType = 'JPG';
        break;
      case 1:
          $imageType = 'GIF';
          break;
    }
    // Determine image orientation and create image
    $ratioWH = ($imageInfo[0]/$w) / ($imageInfo[1]/$h);
    if ($ratioWH>1) {
        $this->image($imagePath, $x, $y+(1-1/$ratioWH)*$h/2, $w, 0, $imageType, $link);
    } else {
        $this->image($imagePath, $x+(1-$ratioWH)*$w/2,   $y, 0, $h, $imageType, $link);
    }
  }
}


$images = $albumobject->getImages();

	//$v = var_export( $images, true );
	//echo $v;
    /**
     * Configure PDF file.  These are all of the parameters that are used to
     * format the proof sheet.  If you'd like to tweak the formatting, here's
     * where to do it.
     */
    switch(getOption('zenproofsheet_size')) {
      case "LTR":
        // Setup for LTR 8.5" x 11" paper (215.9mm x 279.4mm)
        $cfg = array(
          'pageW'        =>   215.9, // mm
          'pageH'        =>   279.4, // mm
          'imageNumW'    =>       5, // integer number
          'imageNumH'    =>       5, // integer number
          'imageSizeW'   =>      36, // mm
          'imageSizeH'   =>      36, // mm
          'marginL'      =>      10, // mm
          'marginR'      =>      10, // mm
          'marginT'      =>      21, // mm (header goes in here)
          'marginB'      =>      20, // mm (footer goes in here)
          'headerSpace'  =>       2, // mm (header to top row of images and to link icon)
          'footerSpace'  =>       2, // mm (bottom row of captions to footer)
          'captionSpace' =>       1, // mm (bottom of image to caption)
          'headerFont'   => array(
            'name'       => 'Arial', // included are Arial/Helvetica, Courier, Times, Symbol, ZapfDingbats
            'size'       =>      14, // pt
            'style'      =>     'B', // combo of B, I, U
            'posn'       =>     'L', // combo of L, C, R
            'r'          =>       0, // Red 0-255
            'g'          =>       0, // Green 0-255
            'b'          =>       0),// Blue 0-255
          'footerFont'   => array(
            'name'       => 'Arial', // included are Arial/Helvetica, Courier, Times, Symbol, ZapfDingbats
            'size'       =>      12, // pt
            'style'      =>     'B', // combo of B, I, U
            'posn'       =>     'R', // combo of L, C, R
            'r'          =>       0, // Red 0-255
            'g'          =>       0, // Green 0-255
            'b'          =>       0),// Blue 0-255
          'captionFont'  => array(
            'name'       => 'Arial', // included are Arial/Helvetica, Courier, Times, Symbol, ZapfDingbats
            'size'       =>       8, // pt
            'style'      =>     'U', // combo of B, I, U
            'posn'       =>     'C', // combo of L, C, R
            'r'          =>       0, // Red 0-255
            'g'          =>       0, // Green 0-255
            'b'          =>     255),// Blue 0-255
        );
        break;
      case "A4":
        // Setup for A4 210mm x 297mm paper (8.27" x 11.69")
        $cfg = array(
          'pageW'        =>     210, // mm
          'pageH'        =>     297, // mm
          'imageNumW'    =>       5, // integer number
          'imageNumH'    =>       6, // integer number
          'imageSizeW'   =>      36, // mm
          'imageSizeH'   =>      36, // mm
          'marginL'      =>       8, // mm
          'marginR'      =>       8, // mm
          'marginT'      =>      19, // mm (header goes in here)
          'marginB'      =>      18, // mm (footer goes in here)
          'headerSpace'  =>       2, // mm (header to top row of images and to link icon)
          'footerSpace'  =>       2, // mm (bottom row of captions to footer)
          'captionSpace' =>       1, // mm (bottom of image to caption)
          'headerFont'   => array(
            'name'       => 'Arial', // included are Arial/Helvetica, Courier, Times, Symbol, ZapfDingbats
            'size'       =>      14, // pt
            'style'      =>     'B', // combo of B, I, U
            'posn'       =>     'L', // combo of L, C, R
            'r'          =>       0, // Red 0-255
            'g'          =>       0, // Green 0-255
            'b'          =>       0),// Blue 0-255
          'footerFont'   => array(
            'name'       => 'Arial', // included are Arial/Helvetica, Courier, Times, Symbol, ZapfDingbats
            'size'       =>      12, // pt
            'style'      =>     'B', // combo of B, I, U
            'posn'       =>     'R', // combo of L, C, R
            'r'          =>       0, // Red 0-255
            'g'          =>       0, // Green 0-255
            'b'          =>       0),// Blue 0-255
          'captionFont'  => array(
            'name'       => 'Arial', // included are Arial/Helvetica, Courier, Times, Symbol, ZapfDingbats
            'size'       =>       8, // pt
            'style'      =>     'U', // combo of B, I, U
            'posn'       =>     'C', // combo of L, C, R
            'r'          =>       0, // Red 0-255
            'g'          =>       0, // Green 0-255
            'b'          =>     255),// Blue 0-255
        );
     // default:
       // throw new Kohana_Exception('unhandled page type: '.$page_type);
    }
//echo var_export( $cfg, true );
 // Here are some other parameters that need defining
    $cfg['footerTextPage']       = 'Page ';   // Note that this text isn't autofixed by translate module
    $cfg['footerTextSlash']      = ' / ';
    $cfg['headerLinkIconPath']   = SERVERPATH.'/plugins/ZenProofsheet/images/ico-link.png';
    $pt2mm                       = 25.4/72;   // 25.4mm=1in=72pt

    // Derive a bunch more parameters.  These are all dependent on the above stuff.
    $cfg['headerH'] = $pt2mm * $cfg['headerFont']['size'];
    $cfg['footerH'] = $pt2mm * $cfg['footerFont']['size'];
    $cfg['captionH'] = $pt2mm * $cfg['captionFont']['size'];
    $cfg['imageSpaceW'] = ($cfg['pageW']-$cfg['marginL']-$cfg['marginR']-$cfg['imageNumW']*$cfg['imageSizeW']) / ($cfg['imageNumW']-1);
    $cfg['imageSpaceH'] = ($cfg['pageH']-$cfg['marginT']-$cfg['marginB']-$cfg['imageNumH']*$cfg['imageSizeH']-$cfg['captionH']-$cfg['captionSpace']) / ($cfg['imageNumH']-1);
    $linkInfo = getimagesize($cfg['headerLinkIconPath']);
    $cfg['headerLinkH'] = $cfg['headerH']; // I'm defining this to be the same as the text, but you can change it here.
    $cfg['headerLinkW'] = $linkInfo[0] / $linkInfo[1] * $cfg['headerLinkH'];
    $cfg['headerW'] = $cfg['pageW']-$cfg['marginL']-$cfg['marginR']-$cfg['headerLinkW']-$cfg['headerSpace'];
    $cfg['footerW'] = $cfg['pageW']-$cfg['marginL']-$cfg['marginR'];
    $cfg['captionW'] = $cfg['imageSizeW']; // I'm defining this to be the same as the image, but you can change it here.
    $cfg['headerX'] = $cfg['marginL'];
    $cfg['headerLinkX'] = $cfg['marginL']+$cfg['headerW'];
    $cfg['footerX'] = $cfg['marginL'];
    $cfg['headerY'] = $cfg['marginT']-$cfg['headerH']-$cfg['headerSpace'];
    $cfg['headerLinkY'] = $cfg['marginT']-$cfg['headerLinkH']-$cfg['headerSpace'];
    $cfg['footerY'] = $cfg['pageH']-$cfg['marginB']+$cfg['footerSpace'];
    $cfg['imageNum'] = $cfg['imageNumW']*$cfg['imageNumH'];

    /**
     * Initialize and build PDF... the main routine.  Note that almost all of the
     * useful configuration parameters are already defined above.
     */
 // Initialize PDF, disable automatic margins and page breaks
    $pdf = new proofsheet_PDF('P', 'mm', array($cfg['pageW'],$cfg['pageH']) );
    $pdf->SetMargins(0,0);
    $pdf->SetAutoPageBreak(0);

//----------------------------------------------//	
    // Build the PDF
//---------------------------------------------//

    $numpages = floor(count($images)/$cfg['imageNum'])+1;
    $i = 0;
//echo 'START LOOP '.$numpages;
global $_zp_current_image;
makeAlbumCurrent( $albumobject );
    foreach($images as $image) {
	$imgpath = ALBUM_FOLDER_SERVERPATH.$albumobject->getFolder().'/'.$image;
	$imgurl = FULLWEBPATH.'/'.$albumobject->getFolder().'/'.$image;
	$zensignature_ss_size_w=800;
$zensignature_ss_size_h=600;

$headerText = $albumobject->getTitle();
$headerLink = FULLWEBPATH.'/'.$albumobject->getFolder();

	while ( next_image( true ) ) {
	if ($_zp_current_image->filename == $image) {
		$list = array(
			 'id' => $_zp_current_image->getID(),
			'albumid' => $_zp_current_image->getAlbum()->getID(),
			'name' => $_zp_current_image->filename,
			'resize' => $_zp_current_image->getCustomImage(null,$zensignature_ss_size_w,$zensignature_ss_size_h,$zensignature_ss_size_w,$zensignature_ss_size_h,null,null,true),
			//'resize' => $_zp_current_image->getSizedImage(800), with watermark
			//'resize' => getURL($_zp_current_image), with watermark
			'resize2' => getURL($_zp_current_image),
			'url' => ( $_zp_current_image->album->name ) . '&image=' . urlencode( $_zp_current_image->filename ) 
		);
		} 
	} //next_image( true )
      // Initialize new pages, add header and footer
      if (($i % $cfg['imageNum'])==0) {
        $pdf->AddPage();
		
        $pdf->printText($headerText, $cfg['headerFont'], $cfg['headerX'], $cfg['headerY'], $cfg['headerW'], $cfg['headerH']);
        $pdf->printImage($cfg['headerLinkIconPath'], $cfg['headerLinkX'], $cfg['headerLinkY'], $cfg['headerLinkW'], $cfg['headerLinkH'], $headerLink);
        $footerText = $cfg['footerTextPage'] . strval(floor($i/$cfg['imageNum'])+1) . $cfg['footerTextSlash'] . strval($numpages);
        $pdf->printText($footerText, $cfg['footerFont'], $cfg['footerX'], $cfg['footerY'], $cfg['footerW'], $cfg['footerH']);
      }
      // Add thumbnail and caption
      $x = $cfg['marginL'] + ($cfg['imageSizeW']+$cfg['imageSpaceW']) * (      $i                    % $cfg['imageNumW']);
      $y = $cfg['marginT'] + ($cfg['imageSizeH']+$cfg['imageSpaceH']) * (floor($i/$cfg['imageNumW']) % $cfg['imageNumH']);
      $pdf->printImage(FULLWEBPATH.str_replace(' ', '%20', $list['resize']), $x, $y, $cfg['imageSizeW'], $cfg['imageSizeH'], null);
      $pdf->printText($image, $cfg['captionFont'], $x, $y+$cfg['imageSizeH']+$cfg['captionSpace'], $cfg['captionW'], $cfg['captionH'], $imgurl);
      // Increment index and loop
	  //echo $list['resize2'].'<br>';
	  //echo var_export( getimagesize(FULLWEBPATH.str_replace(' ', '%20', $list['resize'])), true ).'<br>';
      $i++;
}
$pdfstring = $pdf->Output(ALBUM_FOLDER_SERVERPATH.$albumobject->getFolder().'/'.$albumobject->getTitle().'.pdf','F');
$pdfstring;

    ob_start();
$filedwn = ALBUM_FOLDER_SERVERPATH.$albumobject->getFolder().'/'.$albumobject->getTitle().'.pdf';

    if (file_exists($filedwn)) 
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($filedwn));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filedwn));
        ob_clean();
        flush();
        readfile($filedwn);
		echo 'true';
        exit();
    }
?>
