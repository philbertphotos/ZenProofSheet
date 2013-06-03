<?php
/**
 * 
Proof Sheet is a plugin for Zenphoto which will allow you to a to generate PDF proof sheet/s in A4 or LTR size paper in landscape or portrait orientation.
Place <div>printProofSheet()</div> in your theme where ever you want it to appear.
 *		Something like <?php if (function_exists('printProofSheet')) { ?><?php printProofSheet(); ?><?php  } ?>
 * @package plugins
 */

$plugin_author = "Joseph Philbert";
$plugin_version = '1.6';
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
$albumID = $_zp_current_album->getID();
//<form action="<?php SERVERPATH.'/'.USER_PLUGIN_FOLDER.'/ZenProofsheet/downloadsheet.php'  method="post">
 // <input type="button" value="Download Contact Sheet" /><form method="POST" action="<?php echo FULLWEBPATH.'/albums/'.$_zp_current_album->getFolder().'/'.$_zp_current_album->getTitle().'.pdf'; "></form>
 $pdfdwn = "/albums/".$_zp_current_album->getFolder()."/".$_zp_current_album->getTitle().".pdf";
 $pdfurl = '/plugins/ZenProofsheet/downloadsheet.php';
 $pdfattrib = '?albumobject='.$_zp_current_album->getID();
 $met = 'albumobject='.$albumID;
?>
		<script type="text/javascript">

function downpdf()
{
jQuery.download = function(url, data, method){
	//url and data options required
	
	if( url && data ){ 
		//data can be string of parameters or array/object
		data = typeof data == 'string' ? data : jQuery.param(data);
		//split params into form inputs
		var inputs = '';
		jQuery.each(data.split('&'), function(){ 
			var pair = this.split('=');
			inputs+='<input type="hidden" name="'+ pair[0] +'" value="'+ pair[1] +'" />'; 
		});
		//send request
		jQuery('<form action="'+ url +'" method="'+ (method||'post') +'">'+inputs+'</form>')
		.appendTo('body').submit().remove();
	};
};
$.download('/plugins/ZenProofsheet/downloadsheet.php', '<?php echo $met; ?>', 'POST');
}

</script>

<input type="submit" onclick="downpdf()" value="Download Contact Sheet">

<?php
}
?>
`
