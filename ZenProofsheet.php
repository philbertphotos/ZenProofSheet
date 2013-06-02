<?php
//include(dirname(__FILE__).'/ZenProofsheet/downloadsheet.php');
//ini_set( 'max_execution_time', 60 );
ini_set( "display_errors", "1" );
error_reporting( E_ALL );
/**
 * 
Proof Sheet is a plugin for Zenphoto which will allow you to a to generate PDF proof sheet/s in A4 or LTR size paper in landscape or portrait orientation.
Place <div>printProofSheet()</div> in your theme where ever you want it to appear.
 *		Something like <?php if (function_exists('printProofSheet')) { ?><?php printProofSheet(); ?><?php  } ?>
 * @package plugins
 */

$plugin_author = "Joseph Philbert";
$plugin_version = '1.5';
$plugin_URL = 'http://philbertphotos.github.com/Zenphoto-ProofSheet';
$plugin_description = gettext("Proof Sheet is a plugin for Zenphoto which will allow you to a to generate PDF proof sheet/s in A4 or LTR size paper in landscape or portrait orientation. The PDF is generated on-the-fly and removed after its been downloaded");

$option_interface = 'ZenProofSheetOptions';

//function ZenProofSheetHead(){
//echo SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/ZenProofsheet';
require_once(SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/ZenProofsheet/fpdf/fpdf.php');
//}
/**
 * Plugin option handling class
 *
 */
class ZenProofSheetOptions {
  /**
* Handles custom formatting of options for Admin
*
* @param string $option the option name of the option to be processed
* @param mixed $currentValue the current value of the option (the "before" value)
*/
function handleOption($option, $currentValue) {
}
	function ZenProofSheetOptions() {
		setOptionDefault('zenproofsheet_size', 'A4');
	}

	function getOptionsSupported() {
		return array(										
									
	gettext('Paper Size') => array('key' => 'zenproofsheet_size', 'type' => OPTION_TYPE_SELECTOR, 
				'order' => 1,
				'selections' => array(gettext('A4') => 'A4', gettext('LTR') => 'LTR'),
				'desc' => gettext('Select the desired paper size')),	
		);
	}
}
  
function printProofSheet() {
global $_zp_current_album;
//<form action="<?php SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/ZenProofsheet/downloadsheet.php'  method="post">
 // <input type="button" value="Download Contact Sheet" /><form method="POST" action="<?php echo FULLWEBPATH.'/albums/'.$_zp_current_album->getFolder().'/'.$_zp_current_album->getTitle().'.pdf'; "></form>
?>
		<script type="text/javascript">
function downpdf()
{
    $.ajax(
        {
               type: "POST",
               url: "<?php echo '/plugins/ZenProofsheet/downloadsheet.php?albumobject='.$_zp_current_album->getID(); ?>",
               data: "", // data to send to above script page if any
               cache: false,
               success: function(data)
               {
			   window.location = '<?php echo "/albums/".$_zp_current_album->getFolder()."/".$_zp_current_album->getTitle().".pdf"; ?>';
			   //window.open(location.protocol + '//' + location.hostname + '<?php echo "/albums/".$_zp_current_album->getFolder()."/".$_zp_current_album->getTitle().".pdf"; ?>');
			   //window.open(location.protocol + '//' + location.hostname + '<?php echo "/albums/".$_zp_current_album->getFolder()."/".$_zp_current_album->getTitle().".pdf"; ?>');
                //alert(data);// update code for your page
               }
         });
}

</script>

<input type="submit" onclick="downpdf()" value="Download Contact Sheet">

<?php
}
?>
`