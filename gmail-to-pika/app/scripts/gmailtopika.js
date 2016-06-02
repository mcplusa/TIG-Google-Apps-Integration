var gmailToPika = null;

var GmailToPika = function(){
	var gmail = new Gmail();
	var addedAttachments = false;
	var URL_WS = localStorage.pika_WS;
	var username = localStorage.user;
	var drive_api = URL_WS + "/drive";
	var trackedCompose = null;
	var authToken = '';
	var modal = {
		el : $('#gm-integration-topika'),
		copyButton : $('#gm-integration-topika #copy'),
		attachButton: $('#gm-integration-topika #attachment'),
		searchInput: $('#gm-integration-topika #search-input'),
		introduction: $('#gm-integration-topika #introduction'),
		cases: $('#gm-integration-topika #cases'),
		casesTable: $('#gm-integration-topika #cases-results'),
		searchSpin: $('#gm-integration-topika #searchSpin'),
		copySpin: $('#gm-integration-topika #copySpin'),
		searchAllCases: $('#gm-integration-topika #all-cases'),
		searchMyCases: $('#gm-integration-topika #my-cases'),
		attachments: $('#gm-integration-topika .attachments'),
		attachmentsList: $('#gm-integration-topika .attachments ul'),
		sendEntireThread: $('#gm-integration-topika #send-entire-tread')
	};
	var toPikaButton = {
		content : '<i class="fa fa-paper-plane"></i> To Pika',
		class : 'btn btn-danger toPika',
		toolbarButton : function (){
			resetModal();
			modal.el.modal();
		},
		composeButton : function (){
			resetModal('compose');
			modal.el.modal();
			trackedCompose = $(this).closest('.M9');
		}
	}
	var postData = {
		cases : [],
		emails : '',
		attachments : []
	}
	var showAllCases = false;
	
	var resetModal = function(mode){
		modal.searchInput.val('');
		modal.searchInput.focus();
		modal.introduction.show();
		modal.cases.hide();
		modal.casesTable.bootstrapTable('destroy');
		modal.copyButton.prop('disabled',true);
		modal.copyButton.unbind("click");
		modal.attachments.hide();
		modal.attachmentsList.empty();
		modal.copySpin.hide();
		
		addedAttachments = false;
		showAllCases = false;
		
		postData = {
			cases : [],
			emails : '',
			attachments : []
		}

		if(mode == 'compose'){
			modal.attachButton.hide();
			modal.sendEntireThread.parent().parent().hide();
			modal.copyButton.click(function(){
				var selectedCases = modal.casesTable.bootstrapTable('getSelections');
				trackedCompose.find('.toPika').addClass('disabled');
				trackedCompose.find('.toPika').html('Copied');
				trackedCompose.find('.gU.Up [role="button"]').click(function(){
					postData.cases = selectedCases;
					postData.emails = emailToModel('','',formatEmailContent(trackedCompose.find('[role="textbox"]').html()));
					copy();
				});
				modal.el.modal('hide');
			});
		}else {
			modal.attachButton.show();
			modal.attachButton.prop('disabled',true);
			modal.sendEntireThread.parent().parent().show();
			modal.sendEntireThread.prop('checked',true);
			modal.copyButton.click(function(){
				modal.copySpin.show();
				modal.copyButton.prop('disabled',true);

				setTimeout(function(){
					postData.emails = "";
					postData.cases = modal.casesTable.bootstrapTable('getSelections');
					$(getEmailsContent()).each(function(){
						var t = this.threads;
						var e = '';
						var h = '';
						for(var k in t) {
							e = formatEmailContent(t[k].content_html);
							h = formatHeader(t[k]);

							postData.emails += emailToModel(h,t[k].subject, e);
						}
					});

					copy();
				}, 1000);
			});
			updateAttachButton();
		}
	};

	function formatHeader(e){
	  var h = "";

	  h += e.from;
	  h += ' <' + e.from_email + '> ';
	  h += e.datetime + '\nTo: ';
	  $(e.to).each(function(){
	    h += this + '\n';
	  })

	  return h;
	}

	var formatEmailContent = function(emailHTML){
		var html = document.createElement('div');
		html.innerHTML = emailHTML;
		
		if(html.getElementsByClassName('gmail_quote').length)
			html.getElementsByClassName('gmail_quote')[0].parentNode.remove();
		
		return html.innerHTML
				.replace(/<li>/ig, ' * ')
				.replace(/<br\s*[\/]?>/i, '\n')
				.replace(/<[^>]+>/ig, '')
				.replace(/&[^\s]*/g, '');
	}

	var trackEvents = function(){
		modal.casesTable.on('all.bs.table', function(){
			if(modal.casesTable.bootstrapTable('getSelections').length){
				modal.copyButton.prop('disabled',false);
			}else{
				modal.copyButton.prop('disabled',true);
			}
		});
	};

	var renderAttachments = function(){
		modal.attachmentsList.empty();
		if(!postData.attachments.length){
			modal.attachments.hide();
		}
		$(postData.attachments).each(function(index){
			var item = $('<li class="list-group-item">');
			var removeAttachmentButton = $('<span class="pull-right"><i class="fa fa-close" /></span>');
			removeAttachmentButton.click(function(){
				postData.attachments.splice(index,1);
				renderAttachments();
				updateAttachButton();
			});
			item.append(this.filename);
			item.append(removeAttachmentButton);
			
			modal.attachmentsList.append(item);
		});
	};

	var listAttachments = function(){
		var conversation = gmail.get.selected_emails_data();
		addedAttachments = true;
		$(conversation).each(function(){
			var thread = this.threads;
			var attachment = {};
			for(var key in thread){
				if(thread[key].attachments != ""){
					attachment = {};
					for(attach in thread[key].attachments){
						attachment.filename = thread[key].attachments[attach];
						attachment.downloadLink = "https://mail.google.com/mail/?ui=2&ik="+gmail.tracker.ik+"&view=att&th=" + key + "&attid=0." + (Number(attach) + 1) + "&disp=safe&zw"
						postData.attachments.push(JSON.parse(JSON.stringify(attachment)));
					}
				}
			}
		});
	};

	var updateAttachButton = function(){
		if(!addedAttachments){
			modal.attachButton.find('.text').html("Add Attachments");
		}else if(modal.attachments.is(':visible')){
			modal.attachButton.find('.text').html("Hide Attachments ( "+ postData.attachments.length +" )");
		}else{
			modal.attachButton.find('.text').html("Show Attachments ( "+ postData.attachments.length +" )");
			if(!postData.attachments.length)
				modal.attachButton.prop('disabled',true);
		}
	};

	var cleanThread = function(thread, emailsToKeep){
		for(var key in thread){
			if(!$.inArray(key, emailsToKeep)){
				continue;
			}
			delete thread[key];
		}
	};

	var getEmailsContent = function(){
		var data = gmail.get.selected_emails_data();
		if(!modal.sendEntireThread.is(':checked')){
			if(gmail.check.is_inside_email()){
				var id;
				if($('.adf.ads').index($('.h7 .adf.ads.selected')) != -1){
					id = gmail.get.email_ids()[$('.adf.ads').index($('.h7 .adf.ads.selected'))];
				}else {
					id = checkForwardId(data[0].threads, data[0].last_email);
				}
				cleanThread(data[0].threads, new Array(id));
			}else{
				$(data).each(function(){
					var thread = this.threads;
					cleanThread(thread,new Array(checkForwardId(this.threads, this.last_email)));
				});
			}
		}
		return data;
	};

	var checkForwardId = function(thread, id){
		if((thread[id].subject.toLowerCase().search("fwd:") == 0)
			|| (thread[id].subject.toLowerCase().search("fw:") == 0)){
			return thread[id].reply_to_id;
		}
		return id;
	}

	var emailToModel = function(header,subject, body){
		var email = localStorage.email_model
			.replace("#header", header)
			.replace("#subject", subject)
			.replace("#body",body);

		return email;
	}

	var checkDriveAuth = function(showAuth, callback){
		return ($.ajax({
				type: 'GET',
				url: drive_api,
				async: false,
				headers: {
					"Authorization":authToken
				},
				success: function(data){
					if(data != "authorized"){
						window.open(drive_api  + "/auth?username=" + username, "Request for Authorization", "width=600, height=400, scrollbars=yes");
					}
				}
			}).responseText == "authorized");
	}

	var getFolderId = function(foldersId, c){
		if(undefined != c.google_drive_folder_id && null != c.google_drive_folder_id && c.google_drive_folder_id != ""){
			foldersId.push(c.google_drive_folder_id);
		}else {
			var id;

			$.ajax({
				type: 'POST',
				async: false,
				url: drive_api + "/new_folder",
				headers: {
					"Authorization":authToken
				},
				data: {
					"folder_name": c.case_number
				},
				success: function(data){
					id = data.id;
				},
				dataType: 'json'
			});

			$.ajax({
				type: 'PUT',
				url: URL_WS + "/cases/" + c.case_id,
				contentType: "application/json",
				headers: {
					"Authorization": authToken
				},
				data: JSON.stringify({
					"google_drive_folder_id": id
				})
			});

			foldersId.push(id);
		}
	}

	var copy = function(){
		var success = true;
		var error = "";
		var foldersId = [];

		if(!postData.attachments.length || (postData.attachments.length && checkDriveAuth())){
			
			$(postData.cases).each(function(){
				var c = $(this)[0];

				$.ajax({
					async: false,
					type: 'POST',
					url: URL_WS + '/casenotes',
					contentType: "application/json",
					headers: {
						"Authorization":authToken
					},
					success: function(){
					},
					data: JSON.stringify({
						'case_id': c.case_id,
						'notes': postData.emails
					}),
					dataType: 'json'
				});
				if(postData.attachments.length)
					getFolderId(foldersId, c);
			});

			$(postData.attachments).each(function(){
				var att = $(this)[0];

				var getAttachment = new XMLHttpRequest();
				getAttachment.onload = function() {
			    var params = new FormData();
			    params.append("upfile", getAttachment.response);
			    params.append("file_name", att.filename);
			    if(foldersId.length)
			    	params.append("folder_id", foldersId);

			    var uploadRequest = new XMLHttpRequest();
			    uploadRequest.open('POST', drive_api + "/upload");
			    uploadRequest.setRequestHeader("Authorization", authToken);
			    uploadRequest.send(params);
				};
				getAttachment.responseType = 'blob';
				getAttachment.open('GET', att.downloadLink, true);
				getAttachment.send();
			});

		}else{
			success = false;
		}
		
		copyCallback(success, error);
		
		return false;
	};

	var copyCallback = function(success, errorMsg){
		modal.copySpin.hide();
		modal.copyButton.prop('disabled',false);
		
		if(errorMsg){
			alert(errorMsg);
		}

		if(success){
			modal.el.modal('hide');
		}

	};

	this.toggleAttachments = function(){
		if(!addedAttachments){
			listAttachments();
			updateAttachButton();
		}

		if(postData.attachments.length){
			modal.attachments.toggle(function(){
				renderAttachments();
				updateAttachButton();
			});
		}
	};

	this.displayCases = function(){
		modal.casesTable.bootstrapTable().bootstrapTable('hideLoading');
		modal.introduction.hide();
		modal.cases.show();
		modal.copyButton.prop('disabled',true);
		modal.attachButton.prop('disabled',false);
		trackEvents();
	};


	this.showAllCases = function(allCases){
		if(allCases == undefined){
			allCases = showAllCases;
		}else{
			showAllCases = allCases;
			this.search();
		}

		if(allCases){
			modal.searchAllCases.removeClass('btn-link');
			modal.searchMyCases.addClass('btn-link');
		}else{
			modal.searchAllCases.addClass('btn-link');
			modal.searchMyCases.removeClass('btn-link');
		}
	};

	this.search = function(){
		this.displayCases();
		modal.searchSpin.show();

		var params = "?q="+modal.searchInput.val();
		if(showAllCases)
			params += "&allCases";

		$.ajax({
			url: URL_WS + '/cases'+params,
			headers: {
				"Authorization":authToken
			},
			success: function(data){
				modal.casesTable.bootstrapTable('load', data);
			},
			complete: function(){
				modal.searchSpin.hide();
			},
			dataType: 'json'
		});

		return false;
	};

	this.init = function(){
		var addPikaButton = setInterval(function(){
			if(gmail.dom.composes().length){
				$(gmail.dom.composes()).each(function(){
					if(!this.find('.toPika').length){
						gmail.tools.add_compose_button(this, toPikaButton.content, toPikaButton.composeButton, toPikaButton.class);
					}
				});
			}
					
			if(!$('[gh="mtb"] .toPika').length){
				if($('div[role="checkbox"][aria-checked="true"]').length || gmail.check.is_inside_email()){
					gmail.tools.add_toolbar_button(toPikaButton.content, toPikaButton.toolbarButton, toPikaButton.class);
			
					if(gmail.check.is_inside_email()){
						$('.adf.ads').click(function(){
							$('.adf.ads.selected').removeClass('selected');
							$(this).addClass('selected')
						});
					}
				}
			}
		},500);
		authToken = 'Basic ' + localStorage.auth_token;
	};
}

document.onreadystatechange = function () {
  if (document.readyState == "complete") {
		gmailToPika = new GmailToPika();
		gmailToPika.init();
  }
}