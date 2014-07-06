<?php
/**
 * @package Blue Hat CDN
 * @version 2.9.4
 * @copyright (C) Copyright 2006-2014 Blue Hat Network, BlueHatNetwork.com. All rights reserved.
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.txt

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!class_exists('BlueHatNetworkSetting')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'setting.php';
if(!class_exists('BlueHatNetworkDateTime')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'datetime.php';
if(!class_exists('BlueHatNetworkLanguage')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'language.php';
if(!class_exists('BlueHatNetworkFactory')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'factory.php';
if(!class_exists('BlueHatCDNModelBlueHatCDN')) require BHN_PLUGIN_ADMIN_ROOT.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'bluehatcdn.php';

if(class_exists('BlueHatTurboView')) BlueHatTurboView::checkPlugin();

$enableScanSync = BlueHatCDNModelBlueHatCDN::checkRequirements();
$bhnObj =& BlueHatNetworkFactory::getSyncModel();
$settingObj = new BlueHatNetworkSetting();

if(BlueHatNetworkFactory::isWordPress())
{
	$shouldShowHeader = true;
	
	$helpUrl = 'http://www.bluehatnetwork.com/speed-up-wordpress.html#download';
}
elseif(BlueHatNetworkFactory::isJoomla())
{
	jimport('joomla.version');
	
	$jversion = new JVersion();
	
	if(version_compare($jversion->getShortVersion(), '3.0', '>='))
	{
		$shouldShowHeader = true;
	}
	else
	{
		$shouldShowHeader = false;
	}
	
	$helpUrl = 'http://www.bluehatnetwork.com/speed-up-joomla.html#download';
}

$pendingErrorMessages = BlueHatNetworkSetting::get('pending_error_messages');
?>
<div id="bhncdn_main_container">
	<?php if($shouldShowHeader) { ?>
	<h1 class="bhn_header">Blue Hat Turbo</h1>
	<?php } ?>
	<?php 
	if(!empty($pendingErrorMessages)) { 
		BlueHatNetworkSetting::deleteSetting('pending_error_messages');
	?>
	<div class="bhn_errors">
		<?php echo implode('<br />', $pendingErrorMessages); ?>
	</div>
	<?php } ?>
	<div id="bhncdn_mainpage_tabs">
		<ul>
			<li><a href="#tabs-1"><?php echo BlueHatNetworkLanguage::_('BHN_CONTROL_PANEL'); ?></a></li>
			<li><a href="#tabs-2"><?php echo BlueHatNetworkLanguage::_('BHN_SETTINGS'); ?></a></li>
		</ul>
		<div id="tabs-1">
			<p>
				<span style="float: right;"><button type="button" onclick="javascript: BHN.startScanSync();"><?php echo BlueHatNetworkLanguage::_('BHN_SCAN_SYNC_BUTTON'); ?></button> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_SCAN_SYNC_BUTTON_NOTE'); ?>">?</a></span>
				<span id="file_stats"><?php echo $bhnObj->getFileStats(true); ?></span>
				<div class="bhn-clr">&nbsp;</div>
				<br />
				<table id="files_grid"><tr><td></td></tr></table>
				<div id="file_grid_pager"></div>
				<div id="clear_button_container" <?php if($bhnObj->getNumOfFiles() < 1) { ?>style="display: none;"<?php } ?>>
					<br /><br /><br /><br /><br /><br />
					<button type="button" onclick="javascript: try { BHN.clearIndexData(); } catch(err) { if(String(document.location.href).indexOf('?') > -1){ document.location.href=document.location.href+'&task=bhn_clear_index_data&action=bhn_clear_index_data'; } else { document.location.href=document.location.href+'?task=bhn_clear_index_data&action=bhn_clear_index_data'; } };"><?php echo BlueHatNetworkLanguage::_('BHN_CLEAR_DATA_BUTTON'); ?></button>
				</div>
			</p>
		</div>
		<div id="tabs-2">
			<p>
				<form action="" method="post" id="bhncdn_settings_form" autocomplete="off" onsubmit="return BHN.saveSettings();">
					<table cellspacing="0" cellpadding="2" border="0" class="bhnAdminForm">
						<tr>
							<td class="bhnLabelCell"><label for="cdn_provider"><?php echo BlueHatNetworkLanguage::_('BHN_CDN_PROVIDER'); ?>:</label></td>
							<td>
								<select id="cdn_provider" name="cdn_provider" onchange="BHN.changeCDNProviderDropDown(this.value);" autocomplete="off">
									<option value=""><?php echo BlueHatNetworkLanguage::_('BHN_SELECT_CDN'); ?></option>
									<option value="bhn"<?php if($settingObj->get('cdn_provider') == 'bhn') { ?> selected="selected"<?php } ?>>Blue Hat CDN</option>
									<option value="aws"<?php if($settingObj->get('cdn_provider') == 'aws') { ?> selected="selected"<?php } ?>>Amazon Cloudfront</option>
									<option value="rackspace_cloudfiles"<?php if($settingObj->get('cdn_provider') == 'rackspace_cloudfiles') { ?> selected="selected"<?php } ?>>Rackspace Cloudfiles</option>
									<option value="rackspace_cloudfiles_uk"<?php if($settingObj->get('cdn_provider') == 'rackspace_cloudfiles_uk') { ?> selected="selected"<?php } ?>>Rackspace Cloudfiles UK</option>
								</select>&nbsp;&nbsp;<a href="#" target="_blank" id="get_api_credentials_link"<?php if(!$settingObj->get('cdn_provider')) { ?> style="display: none;"<?php } ?>><?php echo BlueHatNetworkLanguage::_('BHN_GET_API_CREDENTIALS'); ?></a>
							</td>
						</tr>
						<tr>
							<td class="bhnLabelCell"><label for="cdn_username"><?php echo BlueHatNetworkLanguage::_('BHN_CDN_USERNAME'); ?>:</label></td>
							<td><input type="text" size="60" name="cdn_username" value="<?php echo $settingObj->get('cdn_username', ''); ?>" autocomplete="off" /></td>
						</tr>
						<tr>
							<td class="bhnLabelCell"><label for="cdn_api_key"><?php echo BlueHatNetworkLanguage::_('BHN_CDN_API_KEY'); ?>:</label></td>
							<td><input type="password" size="60" name="cdn_api_key" value="<?php echo $settingObj->get('cdn_api_key', ''); ?>" autocomplete="off" /></td>
						</tr>
					</table>
					<div style="margin-left: 95px;">
						<h3><?php echo BlueHatNetworkLanguage::_('BHN_OPTIMIZATION_FEATURES'); ?></h3>
						<table cellspacing="0" cellpadding="2" border="0">
							<?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>
							<tr>
								<td colspan="2" style="color: #696F74;"><?php //echo BlueHatNetworkLanguage::sprintf('BHN_FREE_VERSION_INTRO_TXT', $helpUrl); ?>
								<br /><br />
								<?php echo BlueHatNetworkLanguage::sprintf('BHN_FREE_VERSION_CLICK_HERE_TO_ENABLE_ALL', 'javascript: BHN.enableFeatures();'); ?>
								<br /><br />
								</td>
							</tr>
							<tr>
								<td><input class="all_features" type="checkbox" id="enable_optimized_by_bhn_txt" name="enable_optimized_by_bhn_txt" value="1"<?php if ((int)$settingObj->get('enable_optimized_by_bhn_txt', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: BHN.disableFeatures();" /></td>
								<td class="bhnLabelCell"><label for="enable_optimized_by_bhn_txt"><?php echo BlueHatNetworkLanguage::_('BHN_ENABLE_OPTIMIZED_BY_BHN_TXT'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_ENABLE_OPTIMIZED_BY_BHN_TXT_TOOLTIP'); ?>">?</a></td>
							</tr>
							<?php } else { ?>
							<tr>
								<td colspan="2">
									<?php echo BlueHatNetworkLanguage::sprintf('BHN_FREE_VERSION_CLICK_HERE_TO_ENABLE_ALL', 'javascript: BHN.enableFeatures();'); ?>
									<br /><br />
									<input type="hidden" id="enable_optimized_by_bhn_txt" name="enable_optimized_by_bhn_txt" value="1" />
								</td>
							</tr>
							<?php } ?>
							<tr>
								<td width="21"><input class="feature_checkbox all_features" type="checkbox" id="offload_on_fly" name="offload_on_fly" value="1"<?php if ((int)$settingObj->get('offload_on_fly', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" /></td>
								<td class="bhnLabelCell"><label for="offload_on_fly"><?php echo BlueHatNetworkLanguage::_('BHN_OFFLOAD_ON_THE_FLY'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_OFFLOAD_ON_THE_FLY_TOOLTIP'); ?>">?</a></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox all_features" type="checkbox" id="include_js" name="include_js" value="1"<?php if ((int)$settingObj->get('include_js', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" /></td>
								<td class="bhnLabelCell"><label for="include_js"><?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_JS'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_JS_TOOLTIP'); ?>">?</a></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox all_features" type="checkbox" id="include_css" name="include_css" value="1"<?php if ((int)$settingObj->get('include_css', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" /></td>
								<td class="bhnLabelCell"><label for="include_css"><?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_CSS'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_CSS_TOOLTIP'); ?>">?</a></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox all_features" type="checkbox" id="include_images" name="include_images" value="1"<?php if ((int)$settingObj->get('include_images', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" /></td>
								<td class="bhnLabelCell"><label for="include_images"><?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_IMAGES'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_IMAGES_TOOLTIP'); ?>">?</a></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox all_features" type="checkbox" id="include_swf" name="include_swf" value="1"<?php if ((int)$settingObj->get('include_swf', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" /></td>
								<td class="bhnLabelCell"><label for="include_swf"><?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_SWF'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_SWF_TOOLTIP'); ?>">?</a></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox all_features" type="checkbox" id="include_font" name="include_font" value="1"<?php if ((int)$settingObj->get('include_font', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" /></td>
								<td class="bhnLabelCell"><label for="include_font"><?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_FONTS'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_FONTS_TOOLTIP'); ?>">?</a></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox all_features" type="checkbox" id="optimize_html" name="optimize_html" value="1"<?php if ((int)$settingObj->get('optimize_html', 0) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" /></td>
								<td class="bhnLabelCell"><label for="optimize_html"><?php echo BlueHatNetworkLanguage::_('BHN_OPTIMIZE_HTML'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_OPTIMIZE_HTML_TOOLTIP'); ?>">?</a></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox all_features" type="checkbox" id="include_optimized_by_bhn_html_comment" name="include_optimized_by_bhn_html_comment" value="1"<?php if ((int)$settingObj->get('include_optimized_by_bhn_html_comment', 0) > 0) { ?> checked="checked"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="include_optimized_by_bhn_html_comment"><?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_OPTIMIZED_BY_BHN_HTML_COMMENT'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_INCLUDE_OPTIMIZED_BY_BHN_HTML_COMMENT_TOOLTIP'); ?>">?</a></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="combine_files" name="combine_files" value="1"<?php if ((int)$settingObj->get('combine_files', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="combine_files"><?php echo BlueHatNetworkLanguage::_('BHN_SAFELY_COMBINE_FILES'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_SAFELY_COMBINE_FILES_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="externalize_snippets" name="externalize_snippets" value="1"<?php if ((int)$settingObj->get('externalize_snippets', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="externalize_snippets"><?php echo BlueHatNetworkLanguage::_('BHN_EXTERNALIZE_SNIPPETS'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_EXTERNALIZE_SNIPPETS_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="prefetch_dns" name="prefetch_dns" value="1"<?php if ((int)$settingObj->get('prefetch_dns', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="prefetch_dns"><?php echo BlueHatNetworkLanguage::_('BHN_PREFETCH_DNS'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_PREFETCH_DNS_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="remove_html_comments" name="remove_html_comments" value="1"<?php if ((int)$settingObj->get('remove_html_comments', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="remove_html_comments"><?php echo BlueHatNetworkLanguage::_('BHN_REMOVE_HTML_COMMENTS'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_REMOVE_HTML_COMMENTS_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="remove_inline_type_attr" name="remove_inline_type_attr" value="1"<?php if ((int)$settingObj->get('remove_inline_type_attr', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="remove_inline_type_attr"><?php echo BlueHatNetworkLanguage::_('BHN_REMOVE_INLINE_TYPE_ATTR'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_REMOVE_INLINE_TYPE_ATTR_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							<!--
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="remove_quotes" name="remove_quotes" value="1"<?php if ((int)$settingObj->get('remove_quotes', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="remove_quotes"><?php echo BlueHatNetworkLanguage::_('BHN_REMOVE_QUOTES'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_REMOVE_QUOTES_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							-->
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="shorten_doctype" name="shorten_doctype" value="1"<?php if ((int)$settingObj->get('shorten_doctype', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="shorten_doctype"><?php echo BlueHatNetworkLanguage::_('BHN_SHORTEN_DOCTYPE'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_SHORTEN_DOCTYPE_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="shorten_meta_http_equiv_content_type" name="shorten_meta_http_equiv_content_type" value="1"<?php if ((int)$settingObj->get('shorten_meta_http_equiv_content_type', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="shorten_meta_http_equiv_content_type"><?php echo BlueHatNetworkLanguage::_('BHN_SHORTEN_META_CONTENT_TYPE'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_SHORTEN_META_CONTENT_TYPE_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="convert_absolute_urls" name="convert_absolute_urls" value="1"<?php if ((int)$settingObj->get('convert_absolute_urls', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="convert_absolute_urls"><?php echo BlueHatNetworkLanguage::_('BHN_CONVERT_ABSOLUTE_URLS'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_CONVERT_ABSOLUTE_URLS_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox" type="checkbox" id="auto_sync_on_article_change" name="auto_sync_on_article_change" value="1"<?php if ((int)$settingObj->get('auto_sync_on_article_change', $bhnObj->getDefaultFeatureValue()) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /></td>
								<td class="bhnLabelCell"><label for="auto_sync_on_article_change"><?php echo BlueHatNetworkLanguage::_('BHN_AUTO_SYNC_ON_ARTICLE_CHANGE'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_AUTO_SYNC_ON_ARTICLE_CHANGE_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?></td>
							</tr>
							<tr>
								<td><input class="feature_checkbox all_features" type="checkbox" id="auto_empty_cache" name="auto_empty_cache" value="1"<?php if ((int)$settingObj->get('auto_empty_cache', 1) > 0) { ?> checked="checked"<?php } ?> onclick="javascript: return BHN.toggleFeature();" /></td>
								<td class="bhnLabelCell"><label for="auto_empty_cache"><?php echo BlueHatNetworkLanguage::_('BHN_AUTO_EMPTY_CACHE'); ?></label> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_AUTO_EMPTY_CACHE_TOOLTIP'); ?>">?</a></td>
							</tr>
						</table>
					</div>
					<br />
					<div style="margin-left: 95px;">
						<table id="exclude_files"></table>
						<label for="add_file_pattern_to_exclude" style="font-size: 0.9em;"><?php echo BlueHatNetworkLanguage::_('BHN_FILE_PATH_PATTERN_TO_ADD_EXCLUDE'); ?>:</label><input type="text" id="add_file_pattern_to_exclude" name="add_file_pattern_to_exclude" value="" style="margin-top: 5px;" /><button type="button" onclick="javascript: BHN.addFilePathPatternToExcludeList(BHN.jQueryObj('#add_file_pattern_to_exclude').val());"><?php echo BlueHatNetworkLanguage::_('BHN_FILE_PATH_PATTERN_TO_ADD_EXCLUDE_BTN'); ?></button>
					</div>
					<br />

					<table cellspacing="0" cellpadding="2" border="0" class="bhnAdminFormLight">
						<tr>
							<td class="bhnLabelCell" valign="top" style="padding-top: 5px;"><label for="optimize_database"><?php echo BlueHatNetworkLanguage::_('BHN_OPTIMIZE_DATABASE_TABLES'); ?>:</label></td>
							<td>
								<?php $optimizeDatabase = $settingObj->get('optimize_database', 'daily'); ?>
								<select name="optimize_database" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?>>
									<option value=""<?php if(empty($optimizeDatabase)) { ?> selected="selected"<?php } ?>><?php echo BlueHatNetworkLanguage::_('BHN_NEVER'); ?></option>
									<option value="daily"<?php if($optimizeDatabase == 'daily') { ?> selected="selected"<?php } ?>><?php echo BlueHatNetworkLanguage::_('BHN_DAILY'); ?></option>
									<option value="weekly"<?php if($optimizeDatabase == 'weekly') { ?> selected="selected"<?php } ?>><?php echo BlueHatNetworkLanguage::_('BHN_WEEKLY'); ?></option>
									<option value="monthly"<?php if($optimizeDatabase == 'monthly') { ?> selected="selected"<?php } ?>><?php echo BlueHatNetworkLanguage::_('BHN_MONTHLY'); ?></option>
								</select> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_OPTIMIZE_DATABASE_TABLES_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?><br />
								<span style="font-size: 0.9em; color: grey;"><?php 
								$lastDbOptimizationDate = $settingObj->get('database_optimization_date', BlueHatNetworkLanguage::_('BHN_NEVER'));

								if(preg_match('@^[0-9]+$@', $lastDbOptimizationDate)) $lastDbOptimizationDate = date('F j, Y, g:i a', BlueHatNetworkDateTime::convertGMTUnixTimeStampToLocal($lastDbOptimizationDate));
								
								echo BlueHatNetworkLanguage::sprintf('BHN_DB_LAST_OPTIMIZED', $lastDbOptimizationDate); 
								?></span><br /><br />
							</td>
						</tr>
						<tr>
							<td class="bhnLabelCell" valign="top" style="padding-top: 5px;"><label for="auto_scan_sync_interval"><?php echo BlueHatNetworkLanguage::_('BHN_AUTO_SCAN_SYNC'); ?>:</label></td>
							<td>
								<input type="text" name="auto_scan_sync_interval" value="<?php echo $settingObj->get('auto_scan_sync_interval', 86400); ?>" size="10" <?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>disabled="disabled"<?php } ?> /><?php echo BlueHatNetworkLanguage::_('BHN_SECONDS'); ?> <a href="javascript: void(0);" class="bhn-help-icon" title="<?php echo BlueHatNetworkLanguage::_('BHN_AUTO_SCAN_SYNC_TOOLTIP'); ?>">?</a><?php if($bhnObj->shouldOptimizeHtml() > 0) { ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo BlueHatNetworkLanguage::sprintf('BHN_ONLY_AVAILABLE_IN_PRO_VERSION_TXT', $helpUrl); ?><?php } ?><br />
								<span style="font-size: 0.9em; color: grey;"><?php 
								$lastSyncDate = $settingObj->get('process_id', BlueHatNetworkLanguage::_('BHN_NEVER'));

								if(preg_match('@^[0-9]+$@', $lastSyncDate)) $lastSyncDate = date('F j, Y, g:i a', BlueHatNetworkDateTime::convertGMTUnixTimeStampToLocal($lastSyncDate));

								echo BlueHatNetworkLanguage::sprintf('BHN_LAST_SYNC_DATE', $lastSyncDate); 
								?></span>
							</td>
						</tr>
					</table>
					<br />
					<input type="hidden" id="exclude_file_list" name="exclude_file_list" value="<?php echo $settingObj->get('exclude_files', 'tmp/,logs/,cache/'); ?>" />
					<input type="hidden" name="<?php if(BlueHatNetworkFactory::isWordPress()) { ?>action<?php } elseif(BlueHatNetworkFactory::isJoomla()) { ?>task<?php } ?>" value="bhn_save_settings" />
					<?php if(BlueHatNetworkFactory::isJoomla()) { ?>
					<input type="hidden" name="no_html" value="1" />
					<input type="hidden" name="tmpl" value="component" />
					<?php } ?>
					<button type="button" onclick="javascript: BHN.saveSettings();"><?php echo BlueHatNetworkLanguage::_('BHN_SAVE'); ?></button>
				</form>
			</p>
		</div>
	</div>
	<div style="text-align: center; margin: 10px auto 0 auto; font-size: 1.035em;"><?php 
	if(BlueHatNetworkFactory::isWordPress())
	{
		$url = 'http://wordpress.org/support/view/plugin-reviews/blue-hat-cdn';
	}
	elseif(BlueHatNetworkFactory::isJoomla())
	{
		$url = 'http://extensions.joomla.org/extensions/core-enhancements/performance/content-networking/26509';
	}
	
	echo BlueHatNetworkLanguage::sprintf('BHN_LEAVE_FEEDBACK_TXT', $url); 
	?></div>
	<div id="bhn_loadingbar"></div>
</div>
<?php if(BlueHatNetworkFactory::isWordPress()) { ?>
<style>
	#jGrowl {
		margin-top: 25px !important;
	}
</style>
<?php } ?>
<script>
//<![CDATA[
BHN.jQueryObj(document).ready(function() {
	BHN.serverUri = <?php if(BlueHatNetworkFactory::isWordPress()) { ?>ajaxurl<?php } elseif(BlueHatNetworkFactory::isJoomla()) { ?>'index.php?option=com_bluehatcdn&tmpl=component&no_html=1'<?php } ?>;
	BHN.pluginBasePath = '<?php if(BlueHatNetworkFactory::isWordPress()) { echo plugins_url('/', BHN_PLUGIN_ADMIN_ROOT_FILE); } elseif(BlueHatNetworkFactory::isJoomla()) { ?>components/com_bluehatcdn/<?php } ?>';
	BHN.isEnabled = <?php echo (int)$enableScanSync; ?>;
	
	<?php if($bhnObj->getNumOfFiles() > 0) { ?>
	BHN.loadedInitialSet = true;
	<?php } ?>
	
	BHN.shouldOptimizeHtml = <?php echo (int)$bhnObj->shouldOptimizeHtml(); ?>;
	BHN.startedScanningProcessTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_STARTED_SCANNING_PROCESS_MSG')); ?>';
	BHN.startedSyncProcessTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_STARTED_SYNC_PROCESS_MSG')); ?>';
	BHN.anErrorOcurredtxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_AN_ERROR_OCCURRED')); ?>';
	BHN.alreadyRunningMsg = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_BUSY_SCANNING')); ?>';
	BHN.finishedScanningSyncTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_FINISHED_SCANNING')); ?>';
	BHN.confirmClearIndexDataTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_CLEAR_INDEX_DATA_CONFIRM')); ?>';
	BHN.indexDataClearedSuccessTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_INDEX_DATA_CLEARED_SUCCESSFULLY')); ?>';
	BHN.defaultLoadingMsg = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_LOADING_MSG_DEFAULT')); ?>';
	BHN.defaultSavingMsg = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_SAVING_MSG_DEFAULT')); ?>';
	BHN.manuallyUploadFileConfirmMsgTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_MAUALLY_UPLOAD_FILE_CONFIRM_MSG')); ?>';
	BHN.deleteSelectedFilesConfirmMsgTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_DELETE_SELECTED_FILES_CONFIRM_MSG')); ?>';
	BHN.deleteSelectedFileConfirmMsgTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_DELETE_SELECTED_FILE_CONFIRM_MSG')); ?>';
	BHN.manuallySyncingFileMsg = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_MANUALLY_SYNCING_FILE_MSG')); ?>';
	BHN.manuallySyncingFileSuccessMsg = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_MANUALLY_SYNCING_FILE_SUCCESS_MSG')); ?>';
	BHN.stopScanSyncTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_STOP_SCAN_SYNC_TXT')); ?>';
	BHN.resumingScanSyncTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_RESUMING_SCAN_SYNC_TXT')); ?>';
	BHN.stopScanSyncConfirmTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_STOP_SCAN_SYNC_CONFIRM_TXT')); ?>';
	BHN.scanSyncProcessStoppedSuccessfullyMsg = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_SCAN_SYNC_STOPPED_SUCCESSULLY_MSG')); ?>';
	BHN.helpTxt = '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_HELP_TXT')); ?>';
	BHN.helpUrl = '<?php echo $helpUrl; ?>';
	
	BHN.jQueryObj("#bhncdn_mainpage_tabs").tabs();
	BHN.jQueryObj("#bhncdn_main_container button").button();
	BHN.jQueryObj(document).tooltip();
	
	BHN.jQueryObj('#add_file_pattern_to_exclude').bind('keypress', function (e) {
		if(e.keyCode == 13) {
			e.stopPropagation();
			
			BHN.addFilePathPatternToExcludeList(BHN.jQueryObj('#add_file_pattern_to_exclude').val());
			
			return false;
		}
	});
	
	BHN.changeCDNProviderDropDown(document.getElementById('cdn_provider').value);
	
	BHN.jQueryObj("#files_grid").jqGrid({
		url: BHN.serverUri,
		postData: {
			task: 'bhn_get_files',
			action: 'bhn_get_files',
			tmpl: 'component',
			no_html: 1
		},
		rowNum: BHN.recordsPerPage,
		altRows: false,
		ignoreCase: true,
		pager: '#file_grid_pager',
		mtype: 'POST',
		datatype: 'json',
		width: 'auto',
		height: 'auto',
		autoencode: false,
		viewrecords: true,
		autowidth: true,
		sortname: 'file_mdate',
		sortorder: 'desc',
		multiselect: true,
		emptyrecords: '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_NO_FILES_SCANNED')); ?>',
		colNames:[
			'<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_ID')); ?>', 
			'<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_FILENAME_PATH')); ?>', 
			'<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_FILENAME_PATH')); ?>', 
			'<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_FILETYPE')); ?>', 
			'<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_ORIGINAL_FILESIZE')); ?>',
			'<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_FINAL_FILESIZE')); ?>',
			'<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_SYNCED_HEADER')); ?>'
		],
		cmTemplate: { title: false },
		colModel:[
			{name: 'file_id', index: 'file_id', hidden: true},
			{name: 'file_full_path', index: 'file_full_path', hidden: true},
			{name: 'file_full_path_display', index: 'file_full_path_display', sortable: true, width: 470},
			{name: 'file_extension', index: 'file_extension', sortable: true, align: 'center', width: 110},
			{name: 'file_original_filesize', index: 'file_original_filesize', sortable: true, align: 'center', search: false, width: 110},
			{name: 'file_final_filesize', index: 'file_final_filesize', sortable: true, align: 'center', search: false, width: 110},
			{name: 'file_mdate', index: 'file_mdate', sortable: true, align: 'center', search: false, width: 130}
		],
		caption: "<?php echo BlueHatNetworkLanguage::_('BHN_FILES_GRID_HEADING'); ?>"
	});
	
	BHN.jQueryObj("#files_grid").jqGrid('navGrid','#file_grid_pager', {
		edit: false,		
		add: false,
		del: true,
		view: false,
		search: true,
		searchtext: '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_SEARCH')); ?>',
		refreshtext: '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_REFRESH')); ?>',
		deltext: '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_DELETE')); ?>',
		deltitle: '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_DELETE_SELECTED_ROWS')); ?>',
		delfunc: BHN.deleteSelectedFilesFromCDN
	}, {}, {}, {}, {
		closeAfterSearch: true,
		closeAfterReset: true,
		multipleSearch: false, 
		overlay: false,
		Reset: '<?php echo str_replace('\'', '\\\'', BlueHatNetworkLanguage::_('BHN_RESET_SEARCH')); ?>',
		afterRedraw: function (p) { 
            BHN.jQueryObj("select.selectopts").css('display', 'none');
			
			BHN.jQueryObj('#fbox_files_grid input[type=text]').keydown( function( e ) {
				if (e.which == 13) {
					BHN.jQueryObj("#fbox_files_grid input[type=text]").blur();
					BHN.jQueryObj("#fbox_files_grid_search").click();
					BHN.jQueryObj("#fbox_files_grid input[type=text]").focus();
				}
			});
        }
	}, {closeOnEscape:true});
	
	BHN.jQueryObj("#exclude_files").jqGrid({
		datatype: "local",
		height: 90,
		colNames:['<?php echo BlueHatNetworkLanguage::_('BHN_FILE_FOLDER_PATH_PATTERN'); ?>'],
		colModel:[
			{name: 'name', index: 'name', width: 320}
		],
		caption: "<?php echo BlueHatNetworkLanguage::_('BHN_EXCLUDE_FILES'); ?>",
		cmTemplate: { title: false }
	});
	
	BHN.excludeFilePathPatternList = '<?php echo $settingObj->get('exclude_files', 'tmp/,logs/,cache/'); ?>';
	BHN.refreshExcludeFileGrid();
});
//]]>
</script>
