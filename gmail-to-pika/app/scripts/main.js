/**
 * Main Chrome Extension
 */
var GmailToPikaExtention =  GmailToPikaExtention || {};


GmailToPikaExtention.main = function(){
  GmailToPikaExtention.Gmail = new Gmail();

  GmailToPikaExtention.addPika = setInterval(function(){
    if(!$('[gh="mtb"] .toPika').length){
      if($('div[role="checkbox"][aria-checked="true"]').length || GmailToPikaExtention.Gmail.check.is_inside_email()){
        GmailToPikaExtention.addPikaButton();
      }
    }
  },300);

  GmailToPikaExtention.Gmail.observe.after('open_email', function() {
    if(!$('[gh="mtb"] .toPika').length){
      GmailToPikaExtention.addPikaButton();
    }
  });
};

GmailToPikaExtention.addPikaButton = function(){
  GmailToPikaExtention.Gmail.tools.add_toolbar_button('<i class="fa fa-paper-plane"></i> To Pika', function (){
    GmailToPikaExtention.resetModal();
    $('#gm-integration-topika').modal();
  },'btn btn-danger toPika');
}

document.onreadystatechange = function () {
  if (document.readyState == "complete") {
    GmailToPikaExtention.main();
  }
}

GmailToPikaExtention.resetModal = function(){
  $('#gm-integration-topika #search-input').val("");
  $('#gm-integration-topika #introduction').show();
  $('#gm-integration-topika #cases').hide();
  $('#gm-integration-topika #cases-results').bootstrapTable('destroy');
  GmailToPikaExtention.showAllCases();
}

GmailToPikaExtention.displayCases = function(){
  $('#gm-integration-topika #cases-results').bootstrapTable();
  $('#gm-integration-topika #cases-results').bootstrapTable('hideLoading');
  $('#gm-integration-topika #introduction').hide();
  $('#gm-integration-topika #cases').show();
  GmailToPikaExtention.trackEvents();
}

GmailToPikaExtention.trackEvents = function(){
  $('#gm-integration-topika #copy').prop('disabled',true);
  $('#gm-integration-topika #cases-results').on('check-all.bs.table', function(){
    $('#gm-integration-topika #copy').prop('disabled',false);
  }).on('check.bs.table', function(){
    $('#gm-integration-topika #copy').prop('disabled',false);
  }).on('uncheck-all.bs.table', function(){
    $('#gm-integration-topika #copy').prop('disabled',true);
  }).on('uncheck.bs.table',function(){
    if(!$('#gm-integration-topika #cases-results').bootstrapTable('getSelections').length){
      $('#gm-integration-topika #copy').prop('disabled',true);
    }
  });
}

GmailToPikaExtention.showAllCases = function(allCases){
  if(allCases || typeof allCases === "undefined"){
    $('#gm-integration-topika #all-cases').removeClass('btn-link');
    $('#gm-integration-topika #my-cases').addClass('btn-link');
  }else{
    $('#gm-integration-topika #all-cases').addClass('btn-link');
    $('#gm-integration-topika #my-cases').removeClass('btn-link');
  }

  if(typeof allCases != "undefined"){
    GmailToPikaExtention.search(allCases);
  }
}

GmailToPikaExtention.search = function(allCases){
  GmailToPikaExtention.displayCases();
  $('#gm-integration-topika #spin').show();
  $.ajax({
    url: 'https://fathomless-savannah-1606.herokuapp.com/api/cases',
    success: function(data){
      $('#gm-integration-topika #cases-results').bootstrapTable('load', data);
    },
    complete: function(){
      $('#gm-integration-topika #spin').hide();
    },
    dataType: 'json'
  });
  
  return false;
}