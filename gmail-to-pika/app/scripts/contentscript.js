/**
 * Add a function and use it to inject classes
 */

var GmailToPikaLoader = {
    inject : {
      JS : function (scriptPath) {
        var script = document.createElement('script');
        script.src = chrome.extension.getURL(scriptPath);
        (document.head || document.documentElement).appendChild(script);
      },
      CSS : function (cssPath) {
        var link = document.createElement('link');
        link.href = chrome.extension.getURL(cssPath);
        link.type = "text/css";
        link.rel = "stylesheet";
        (document.head || document.documentElement).appendChild(link);
      },
      HTML : function(htmlPath){
        var div = document.createElement('div');
        document.body.appendChild(div);
        
        var xhr = new XMLHttpRequest();
        xhr.onload = function () {
            div.innerHTML = this.response;
        };

        xhr.open('GET', chrome.extension.getURL(htmlPath), true);
        xhr.send(); 
      }
    },

    mathLocation : function(location){
      return (document.location.pathname.indexOf(location) != -1);
    },

    config : function(){
      chrome.storage.sync.get({
        'url': '',
        'user' : '',
        'password' : '',
        'email_model' : ''
        }, function(items) {
          localStorage.setItem('pika_WS',items.url);
          localStorage.setItem('email_model',items.email_model);
          localStorage.setItem('auth_token',btoa(items.user+":"+items.password));
          localStorage.setItem('user',items.user);
      });
    },

    load : function(){
      //Add components [JQuery, GmailJS, Bootstrap, BootstrapTable]
      this.inject.JS('scripts/vendorscripts.js');
      //Add and initialize Gmail To Pika
      if(this.mathLocation('mail')) this.inject.JS('scripts/gmailtopika.js');
      //Add and initialize Calendar to Tickler
      if(this.mathLocation('calendar')) this.inject.JS('scripts/calendartotickler.js');

      //Add Font-Awesome CSS
      this.inject.CSS('styles/font-awesome.min.css');
      //Add Modal CSS
      this.inject.CSS('styles/modal.css');
      //Add our css
      this.inject.CSS('styles/main.css');

      //Add modal
      this.inject.HTML('modal.html');
    }
}

GmailToPikaLoader.load();
GmailToPikaLoader.config();