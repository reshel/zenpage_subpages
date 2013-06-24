<?php
/**
 * Provides a content macro to print excerpts of the direct subpages (1 level) of the current Zenpage page:
 * <div class='pageexcerpt'>
 * 	<h3>page title</h3>
 * 	<p>page content excerpt</p>
 * 	<p>read more</p>
 * </div>
 * 
 * 
 * Content macro:
 * [SUBPAGES <headline> <excerpt length> <readmore text> <shortenindicator text>]
 * All are optional, set to empty quotes ('') if you only want to set the last one for example.
 *
 * @license GPL v3 
 * @author Malte Müller (acrylian)
 *
 * @package plugins
 * @subpackage misc
 */
$plugin_is_filter = 9|THEME_PLUGIN|ADMIN_PLUGIN;
$plugin_description = gettext('A plugin to print Zenpagae subpages of the current page');
$plugin_author = 'Malte Müller (acrylian)';
$plugin_version = '1.0';

zp_register_filter('content_macro','zenpageSubpages::subpages_macro');


class zenpageSubpages {
	
	function __construct() {
		
	}
 /* Gets the html setup for the subpage list
 	* @param string $header What to use as headline (h1 - h6)
  * @param string $excerptlength The length of the page content, if nothing specifically set, the plugin option value for 'news article text length' is used
 	* @param string $readmore The text for the link to the full page. If empty the read more setting from the options is used.
 	* @param string $shortenindicator The optional placeholder that indicates that the content is shortened, if this is not set the plugin option "news article text shorten indicator" is used.
  * @return string
  */
	static function getSubPagesHTML($header = 'h3', $excerptlength = NULL, $readmore = NULL, $shortenindicator = NULL) {
		global $_zp_current_zenpage_page;
		$html = '';
		if (empty($readmore)) {
			$readmore = get_language_string(ZP_READ_MORE);
		}
		$pages = $_zp_current_zenpage_page->getPages();
		$subcount = 0;
		if (empty($excerptlength)) {
			$excerptlength = ZP_SHORTEN_LENGTH;
		}
		if(in_array($header,array('h1','h2','h3','h4','h5','h6'))) {
			$headline = $header;
		} else {
			$headline = 'h3';
		}
		foreach ($pages as $page) {
			$pageobj = new ZenpagePage($page['titlelink']);
			if ($pageobj->getParentID() == $_zp_current_zenpage_page->getID()) {
				$subcount++;
				$pagetitle = html_encode($pageobj->getTitle());
				$html .= '<div class="pageexcerpt">';
				$html .= '<'.$headline.'><a href="' . html_encode(getPageLinkURL($pageobj->getTitlelink())) . '" title="' . strip_tags($pagetitle) . '">' . $pagetitle . '</a></'.$headline.'>';
				$pagecontent = $pageobj->getContent();
				if ($pageobj->checkAccess()) {
					$html .= getContentShorten($pagecontent, $excerptlength, $shortenindicator, $readmore, getPageLinkURL($pageobj->getTitlelink()));
				} else {
					$html .= '<p><em>' . gettext('This page is password protected') . '</em></p>';
				}
				$html .= '</div>';
			}
		}
		return $html;
	}
	
 /*
	* macro definition
	* @param array $macros
	* return array
	*/
	static function subpages_macro($macros) {
		$macros['SUBPAGES'] = array(
					'class'=>'function',
					'params'=> array('string*','int*','string*','string*'), 
					'value'=>'zenpageSubpages::getSubPagesHTML',
					'owner'=>'zenpageSubpages',
					'desc'=>gettext('Prints subpages of a Zenpage: Headline h1-h6 to use (%1), excerpt lenght (%2), readmore text (%3), shorten indicator text (%4). All optional, leave empty with empty quotes of you only need to set the last ones')
				);
		return $macros;
	}

} // class end

