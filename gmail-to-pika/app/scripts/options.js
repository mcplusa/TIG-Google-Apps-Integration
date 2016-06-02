function validate_url(){
  var regexHttp = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
  $('#url').parent().removeClass('has-error');
  $('#url').parent().removeClass('has-success');

  var url = document.getElementById('url').value.replace(/ /g,'');
  if(regexHttp.test(url)){
    $('#url').parent().addClass('has-success');
    if(url.substr(url.length - 1) == "/")
      url = url.substr(0,url.length - 1);
  }else{
    $('#url').parent().addClass('has-error');
  }

  document.getElementById('url').value = url;
  return url;
}

function validate_field(field){
  field = "#" + field;
  $(field).parent().removeClass('has-error');
  $(field).parent().removeClass('has-success');

  var value = $(field).val();

  if(!value.length || !value.trim()){
    $(field).parent().addClass('has-error');
  }else{
    $(field).parent().addClass('has-success');
  }

  return value;
}

function save_options() {
  var url = validate_url();
  var user = validate_field('user');
  var password = validate_field('password');
  var email_model = validate_field('email_model');

  if(!$('.has-error').length){
    chrome.storage.sync.set({
      'url': url,
      'user' : user,
      'password' : password,
      'email_model': email_model
      }, function() {
        var status = document.getElementById('status');
        status.textContent = 'Options saved.';
        setTimeout(function() {
          status.textContent = '';
        }, 1000);
    });
  }else{
    $('.has-error [data-toggle="tooltip"]').tooltip('show');
    setTimeout(function() {
      $('[data-toggle="tooltip"]').tooltip('hide');
    }, 2000);
  }
}

function restore_options() {
  var manifest = chrome.runtime.getManifest();
  $('version n').html(manifest.version);

  chrome.storage.sync.get({
    'url': '',
    'user' : '',
    'password' : '',
    'email_model' : ''
    }, function(items) {
      document.getElementById('url').value = items.url;
      document.getElementById('user').value = items.user;
      document.getElementById('password').value = items.password;
      if(items.email_model)
        document.getElementById('email_model').value = items.email_model;
  });
}
document.addEventListener('DOMContentLoaded', restore_options);
document.getElementById('url').addEventListener('blur',validate_url);
document.getElementById('user').addEventListener('blur',function(){
  validate_field('user');
});
document.getElementById('password').addEventListener('blur',function(){
  validate_field('password');
});
document.getElementById('email_model').addEventListener('blur',function(){
  validate_field('email_model');
});
document.getElementById('save').addEventListener('click',save_options);

$('[data-toggle="popover"]').popover({
  html: true,
  trigger: 'click hover focus'
});

$('[data-toggle="tooltip"]').tooltip({
  trigger: 'manual'
});