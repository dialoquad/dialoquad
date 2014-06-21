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

var BHN = {
	serverUri: null,
	startedScanningProcessTxt: null,
	startedSyncProcessTxt: null,
	finishedScanningSyncTxt: null,
	confirmClearIndexDataTxt: null,
	indexDataClearedSuccessTxt: null,
	anErrorOcurredtxt: null,
	alreadyRunningMsg: null,
	stopScanMonitor: false,
	busy: false,
	excludeFilePathPatternList: '',
	timer: null,
	recordsPerPage: 30,
	loadedInitialSet: false,
	loadedInitialSetSync: false,
	isEnabled: 0,
	shouldOptimizeHtml: 1,
	helpUrl: null,
	helpTxt: null,
	defaultLoadingMsg: null,
	defaultSavingMsg: null,
	manuallyUploadFileConfirmMsgTxt: null,
	deleteSelectedFilesConfirmMsgTxt: null,
	deleteSelectedFileConfirmMsgTxt: null,
	manuallySyncingFileMsg: null,
	manuallySyncingFileSuccessMsg: null,
	scanSyncCompletedAlready: false,
	stopScanSyncTxt: null,
	resumingScanSyncTxt: null,
	stopScanSyncConfirmTxt: null,
	scanSyncProcessStoppedSuccessfullyMsg: null,
	jQueryObj: null,
	pluginBasePath: null,
	zombieIsRestarting: false,
	isZombieTempCounter: 0,
	
	changeCDNProviderDropDown: function (newProvider) {
		if (!newProvider || newProvider == '') {
			BHN.jQueryObj('#get_api_credentials_link').css('display', 'none');
		} else {
			BHN.jQueryObj('#get_api_credentials_link').css('display', 'inline-block');
			
			if (newProvider == 'bhn') {
				BHN.jQueryObj('#get_api_credentials_link').attr('href', 'https://www.bluehatnetwork.com/my-account/cdn-buckets.html');
			} else if (newProvider == 'aws') {
				BHN.jQueryObj('#get_api_credentials_link').attr('href', 'https://console.aws.amazon.com/iam/home?#security_credential');
			} else if (newProvider == 'rackspace_cloudfiles') {
				BHN.jQueryObj('#get_api_credentials_link').attr('href', 'http://tracking.rackspace.com/SHDA');
			} else if (newProvider == 'rackspace_cloudfiles_uk') {
				BHN.jQueryObj('#get_api_credentials_link').attr('href', 'http://tracking.rackspace.com/SHDA');
			}
		}
	},
	
	addFilePathPatternToExcludeList: function (pattern) {
		BHN.excludeFilePathPatternList = pattern+','+BHN.excludeFilePathPatternList;
		BHN.refreshExcludeFileGrid();
		BHN.jQueryObj('#add_file_pattern_to_exclude').val('');
	},
	
	deleteFilePathPatternFromExcludeList: function (pattern) {
		var excludedFilesData = BHN.excludeFilePathPatternList.split(",");
		var newValue = '';

		for(var i = 0; i < excludedFilesData.length; i++)
		{
			if (excludedFilesData[i] != pattern) {
				newValue += excludedFilesData[i]+',';
			}
		}
		
		newValue = newValue.replace(/,$/, '');
		
		BHN.excludeFilePathPatternList = newValue;
		
		BHN.refreshExcludeFileGrid();
	},
	
	refreshExcludeFileGrid: function () {
		BHN.jQueryObj("#exclude_files").jqGrid("clearGridData", true);
		
		BHN.jQueryObj('#exclude_file_list').val(BHN.excludeFilePathPatternList);
		
		var excludedFilesData = BHN.excludeFilePathPatternList.split(",");
		var excludedFilesDataFinal = [];

		for(var i = 0; i < excludedFilesData.length; i++)
		{
			excludedFilesDataFinal.push({name: excludedFilesData[i]+' <a style="float: right; margin-right: 3px;" href="javascript: void(0);" onclick="BHN.deleteFilePathPatternFromExcludeList(\''+excludedFilesData[i]+'\');"><img src="'+BHN.pluginBasePath+'assets/images/icons/x.png" width="12" height="12" border="0" alt="" align="absmiddle" /></a>'});
		}

		for(var i = 0 ;i <= excludedFilesDataFinal.length; i++)
		{
			BHN.jQueryObj("#exclude_files").jqGrid('addRowData', i+1, excludedFilesDataFinal[i]);
		}
	},
	
	startScanSync: function () {
		var scanSyncStatusWnd = BHN.startedScanningProcessTxt+'<br /><br /><div id="status_msg">';
		
		if (BHN.busy) {
			scanSyncStatusWnd += BHN.resumingScanSyncTxt;
		}
		
		scanSyncStatusWnd += '</div><div id="sync_status_msg"></div><div id="completed_status_msg"></div><br /><button type="button" onclick="javascript: BHN.stopScanSync();">'+BHN.stopScanSyncTxt+'</button>';
		
		if (BHN.isEnabled < 1) {
			alert(BHN.anErrorOcurredtxt);
		} else if (!BHN.busy) {
			BHN.busy = true;
			BHN.stopScanMonitor = false;
			
			if (String(BHN.jQueryObj('#clear_button_container').css('display')).indexOf('none') > -1) {
				BHN.jQueryObj('#clear_button_container').css('display', 'block');
			}
			
			BHN.jQueryObj.jGrowl(scanSyncStatusWnd, { sticky: true });
			
			BHN.startScan();
			
			setTimeout('BHN.monitorScanSyncStatus();', 1000);
		} else if (BHN.jQueryObj('#sync_status_msg').length < 1) {
			BHN.jQueryObj.jGrowl(scanSyncStatusWnd, { sticky: true });
		}
	},
	
	stopScanSync: function () {
		var answer = confirm(BHN.stopScanSyncConfirmTxt);
		
		if (answer) {
			BHN.busy = false;
			BHN.stopScanMonitor = true;
			
			if (BHN.timer) {
				clearTimeout(BHN.timer);
				BHN.timer = null;
			}
			
			BHN.jQueryObj("div.jGrowl").jGrowl("close");
			
			BHN.jQueryObj.post(BHN.serverUri, {task: 'bhn_stop_scan_sync_process', action: 'bhn_stop_scan_sync_process'}, function (data) {
				BHN.jQueryObj.jGrowl(BHN.scanSyncProcessStoppedSuccessfullyMsg);
				
				BHN.updateFileStats();
				
				BHN.jQueryObj("#files_grid").trigger("reloadGrid", [{page:1}]);
			});
		}
	},
	
	monitorScanSyncStatus: function () {
		BHN.jQueryObj.post(BHN.serverUri, {task: 'bhn_get_scan_sync_status', action: 'bhn_get_scan_sync_status'}, function (data) {
			if(data) {
				if (BHN.busy) {
					if (BHN.isZombieTempCounter > 1 && parseInt(data.is_zombie) > 0 && !BHN.zombieIsRestarting) {
						BHN.zombieIsRestarting = true;
						
						BHN.isZombieTempCounter = 0;
						
						BHN.startScan(null, null, null, 1);
						
						setTimeout('BHN.zombieIsRestarting = false;', 10000);
					} else if (parseInt(data.is_zombie) > 0 && !BHN.zombieIsRestarting) {
						BHN.isZombieTempCounter += 1;
					}
					
					if (String(BHN.jQueryObj('#file_stats').html()).replace(/[^0-9a-zA-Z]+/g, "") != String(data.file_stats).replace(/[^0-9a-zA-Z]+/g, "")) {
						BHN.jQueryObj("#file_stats").html(data.file_stats);
						BHN.jQueryObj("#file_stats").fadeOut(100).fadeIn(100);
					}
					
					if (data.process_mode == 'scan') {
						if(!BHN.loadedInitialSet && data.status_msg) {
							BHN.loadedInitialSet = true;
							setTimeout('BHN.jQueryObj("#files_grid").trigger("reloadGrid", [{page:1}]);', 7000);
						}
						
						var statusMsgWrapper = BHN.jQueryObj('#status_msg');
						
						if (statusMsgWrapper.length > 0 && String(data.status_msg).length > 1) {
							statusMsgWrapper.html(data.status_msg);
						}
					} else if(data.process_mode == 'sync') {
						if(!BHN.loadedInitialSetSync && data.status_msg) {
							BHN.loadedInitialSetSync = true;
							setTimeout('BHN.jQueryObj("#files_grid").trigger("reloadGrid", [{page:1}]);', 15000);
						}
						
						var syncStatusMsgWrapper = BHN.jQueryObj('#sync_status_msg');
						var scanStatusMsgWrapper = BHN.jQueryObj('#status_msg');
						
						if (syncStatusMsgWrapper.length > 0 && scanStatusMsgWrapper.length > 0 && String(data.status_msg).length > 1) {
							if (BHN.jQueryObj.trim(data.status_msg) != BHN.jQueryObj.trim(scanStatusMsgWrapper.html())) {
								if (String(scanStatusMsgWrapper.html()).length > 0) {
									syncStatusMsgWrapper.html('<br />'+data.status_msg);
								} else {
									syncStatusMsgWrapper.html(data.status_msg);
								}
							}
						}
					} else if(data.process_mode == 'completed') {
						var completedStatusMsgWrapper = BHN.jQueryObj('#completed_status_msg');
						var syncStatusMsgWrapper = BHN.jQueryObj('#sync_status_msg');
						var scanStatusMsgWrapper = BHN.jQueryObj('#status_msg');
						
						if (data.status_msg && completedStatusMsgWrapper.length > 0 && syncStatusMsgWrapper.length > 0 && scanStatusMsgWrapper.length > 0) {
							if (BHN.jQueryObj.trim(data.status_msg) != BHN.jQueryObj.trim(syncStatusMsgWrapper.html()) && BHN.jQueryObj.trim(data.status_msg) != BHN.jQueryObj.trim(scanStatusMsgWrapper.html())) {
								completedStatusMsgWrapper.html(data.status_msg);
							}
						}
						
						BHN.scanSyncCompleted();
					}
				}
			}
		}, "json").always(function () {
			if (BHN.busy && !BHN.stopScanMonitor) {
				BHN.timer = setTimeout('BHN.monitorScanSyncStatus();', 1400);
			}
		});
	},
	
	startScan: function (fileToSync, successCallbackFunc, alwaysFunc, suppressResumeTxt) {
		BHN.jQueryObj.ajax({
			type: "POST",
			url: BHN.serverUri,
			dataType: "json",
			timeout: 0,
			cache: false,
			success: function (data) {
				if (successCallbackFunc) {
					successCallbackFunc();
				}
			},
			data: {
				task: 'bhn_scan_sync_files',
				action: 'bhn_scan_sync_files',
				file_to_sync: fileToSync,
				suppress_resume_txt: suppressResumeTxt
			}
		}).always(function () {
			if (alwaysFunc) {
				alwaysFunc();
			}
		});
	},
	
	scanSyncCompleted: function () {
		if (!BHN.scanSyncCompletedAlready && BHN.busy) {
			BHN.scanSyncCompletedAlready = true;
			BHN.busy = false;
			
			if (BHN.timer) {
				clearTimeout(BHN.timer);
				BHN.stopScanMonitor = true;
				BHN.timer = null;
			}

			BHN.jQueryObj.jGrowl(BHN.finishedScanningSyncTxt, { sticky: true });

			var alertFunc = function () {
				alert(BHN.finishedScanningSyncTxt);

				location.reload(true);
			};

			setTimeout(alertFunc, 2000);
		}
	},
	
	clearIndexData: function () {
		var answer = confirm(BHN.confirmClearIndexDataTxt);
		
		if (answer) {
			BHN.busy = false;
			BHN.stopScanMonitor = true;
			
			if (BHN.timer) {
				clearTimeout(BHN.timer);
				BHN.timer = null;
			}
			
			alert(BHN.indexDataClearedSuccessTxt);
			
			if(String(BHN.serverUri).indexOf('?') > -1) {
				document.location.href=BHN.serverUri+'&task=bhn_clear_index_data&action=bhn_clear_index_data';
			} else {
				document.location.href=BHN.serverUri+'?task=bhn_clear_index_data&action=bhn_clear_index_data';
			}
		}
	},
	
	toggleFeature: function () {
		var result = true;
		
		if (BHN.shouldOptimizeHtml > 0) {
			if (document.getElementById('enable_optimized_by_bhn_txt').checked) {
				result = true;
			} else {
				result = false;
				
				alert(BHN.helpTxt);
			}
		}
		
		return result;
	},
	
	disableFeatures: function () {
		BHN.jQueryObj('input.feature_checkbox').prop('checked', false);
	},
	
	enableFeatures: function () {
		if (BHN.shouldOptimizeHtml > 0) {
			BHN.jQueryObj('input.all_features').prop('checked', true);
		} else {
			BHN.jQueryObj('input.feature_checkbox').prop('checked', true);
		}
	},
	
	saveSettings: function () {
		BHN.startProgressBar();
		
		BHN.jQueryObj.post(BHN.serverUri, BHN.jQueryObj('form#bhncdn_settings_form').serialize(), function (responseData, textStatus, jqXHR) {
			BHN.jQueryObj.jGrowl(responseData.msg, { life: 5000 });
			
			BHN.updateFileStats();
			
			BHN.jQueryObj("#files_grid").trigger("reloadGrid", [{page:1}]);
			
			BHN.loadedInitialSet = false;
			BHN.loadedInitialSetSync = false;
		}).always(function () {
			BHN.hideProgressBar();
		});
		
		return false;
	},
	
	startProgressBar: function (msg) {
		msg = msg || BHN.defaultSavingMsg;
		
		var loadingBarElement = BHN.jQueryObj("#bhn_loadingbar");
		
		loadingBarElement.css("top", Math.max(0, ((BHN.jQueryObj(window).height() - loadingBarElement.outerHeight())/2) + BHN.jQueryObj(window).scrollTop()) + "px");
		loadingBarElement.css("left", Math.max(0, ((BHN.jQueryObj(window).width() - loadingBarElement.outerWidth())/2) + BHN.jQueryObj(window).scrollLeft()) + "px");
		loadingBarElement.css('display', 'block');
		
		loadingBarElement.html(msg);
	},
	
	hideProgressBar: function () {
		BHN.jQueryObj("#bhn_loadingbar").css('display', 'none');
	},
	
	manuallyUploadFile: function (fileToUpload) {
		var answer = confirm(BHN.manuallyUploadFileConfirmMsgTxt);
		
		if (answer) {
			BHN.jQueryObj.jGrowl(BHN.manuallySyncingFileMsg+'<br />'+fileToUpload, { life: 10000 });
			
			BHN.startScan(fileToUpload, function () {
				// on success function
			}, function () {
				// always function
				BHN.jQueryObj.jGrowl(fileToUpload+' '+BHN.manuallySyncingFileSuccessMsg, { life: 4000 });
				
				BHN.updateFileStats();
				
				BHN.jQueryObj("#files_grid").trigger("reloadGrid");
			});
		}
	},
	
	deleteSelectedFilesFromCDN: function () {
		var answer = confirm(BHN.deleteSelectedFilesConfirmMsgTxt);
		
		if (answer) {
			BHN.startProgressBar(BHN.defaultLoadingMsg);
			
			var filesGrid = BHN.jQueryObj('#files_grid');
			var selectedRows = filesGrid.jqGrid('getGridParam', 'selarrrow');
			
			if (selectedRows.length > 0) {
				var filePathsList = '';
				
				selectedRows.forEach(function (rowId) {
					filePathsList += filesGrid.jqGrid('getCell', rowId, 'file_full_path')+',';
				});
				
				BHN.jQueryObj.post(BHN.serverUri, {task: 'bhn_delete_selected_files_from_cdn', action: 'bhn_delete_selected_files_from_cdn', file_paths_list: filePathsList}, function (data) {
					if (data) {
						BHN.jQueryObj.jGrowl(data.msg, { life: 4000 });
						
						BHN.updateFileStats();
						
						BHN.jQueryObj("#files_grid").trigger("reloadGrid");
					} else {
						BHN.jQueryObj.jGrowl('<span style="color: red;">'+BHN.anErrorOcurredtxt+'</span>', { sticky: true });
					}
				}, "json").always(function () {
					BHN.hideProgressBar();
				});
			}
		}
	},
	
	updateFileStats: function () {
		BHN.jQueryObj.post(BHN.serverUri, {task: 'bhn_get_scan_sync_status', action: 'bhn_get_scan_sync_status'}, function (data) {
			if(data) {
				if (String(BHN.jQueryObj('#file_stats').html()).replace(/[^0-9a-zA-Z]+/g, "") != String(data.file_stats).replace(/[^0-9a-zA-Z]+/g, "")) {
					BHN.jQueryObj("#file_stats").html(data.file_stats);
					BHN.jQueryObj("#file_stats").fadeOut(100).fadeIn(100);
				}
			}
		}, "json");
	}
};

BHN.jQueryObj = jQuery.noConflict(true);