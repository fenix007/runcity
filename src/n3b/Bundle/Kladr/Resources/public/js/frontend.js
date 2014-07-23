// не даем сабмититься плейсхолдерам
$('form').live('submit', function(){
  $(this).find('input[placeholder]').each(function(){
    var el = $(this);
    if (el.val() == el.attr('placeholder')) {
      el.val('');
    }
  });
  $(this).find('textarea[placeholder]').each(function(){
    var el = $(this);
    if (el.val() == el.attr('placeholder')) {
      el.val('');
    }
  });
});

if (typeof(console) == 'undefined') {
  var console = {
    'log' : function(){}
  };
}

//AJAX LOADER
function ajaxLoader(target){
  var body = $('body');
  var offset, targetSize, position;
  if (target) {
    offset = target.offset();
    targetSize = {
      'w':target.innerWidth(),
      'h':target.innerHeight()
    };
    position = 'absolute';
  } else {
    offset = {'top':0, 'left':0}
    targetSize = {
      'w':$(window).width(),
      'h':$(window).height()
    };
    position = 'fixed';
  }
  
  this.div = $('<div></div>');
  this.div.attr('class', 'ajaxLoader');

  this.div.css({
    'position' : position,
    'width' : targetSize.w,
    'height' : targetSize.h,
    'left' : offset.left,
    'top' : offset.top,
    'zIndex' : 1000
  });
  body.append(this.div);
}
ajaxLoader.prototype.div = null;
ajaxLoader.prototype.remove = function() {
  this.div.remove();
}

jQuery.ajaxSettings.showLoader = true;
jQuery.ajaxSettings.loaderTarget = null;
jQuery.ajaxPrefilter(function(options, originalOptions, jqXHR){
  if (options.showLoader) {
    var loader = new ajaxLoader(options.loaderTarget);
    jqXHR.complete(function(){
      loader.remove();
    });
  }
});
//END OF AJAX LOADER


function isJsonResponse(jqXHR) {
  return jqXHR.getResponseHeader('Content-type').search(/json/) != -1;
}

$('*').live('click.prevent', function(){return false;});
$(function(){
  $('*').die('click.prevent');
});

function delete_confirmation(element, message) {
  var obj = new popupConfirm({
    'title' : 'Предупреждение',
    'text' : message,
    'okLabel' : 'Да',
    'cancelLabel' : 'Не удалять',
    'okCallback' : function(){
      var action_url = element.href;
      var csrf_token = element.hash.substr(1);
      var f = document.createElement('form'); 
      f.style.display = 'none'; 
      element.parentNode.appendChild(f); 
      f.method = 'post';
      f.action = action_url;
      var m = document.createElement('input'); 
      m.setAttribute('type', 'hidden'); 
      m.setAttribute('name', 'sf_method'); 
      m.setAttribute('value', 'delete'); 
      f.appendChild(m);
      m = document.createElement('input'); 
      m.setAttribute('type', 'hidden'); 
      m.setAttribute('name', '_csrf_token'); 
      m.setAttribute('value', csrf_token); 
      f.appendChild(m);
      f.submit();
    }
  });  
}

$(document).ready(function() {
  $('.l-mid_homepage.index .b-step-menu li.next a').click(function(){
    $('.main_address_form button').click();
    return false; 
  });
  if ($('.is_orderWR_success').length)
  {
    var obj = new popupAjaxNoBodyClick($('.orderWR_success_content').html());
    obj.show();
    //obj.html($('.orderWR_success_content').html());    
  }
  
  $('a.error_feedback_load').click(function() {
    var url = this.href;
    $.get(url, function(data) {
      var obj = new popupAjax(data);
      obj.show();
      var initFeedbackForm = function(){
        $('form.error_feedback').submit(function() {
          var query = $(this).serialize();
          $.post($(this).attr('action'),query, function(data){
            obj.html(data);
            initFeedbackForm();
          })
          return false;
        });
      };
      initFeedbackForm();
    });
    return false;
  });
    $('a.corporate-popup').click(function() {
        var url = this.href;
        $.get(url, function(data) {
            var obj = new popupAjax(data);
            obj.show();
            var initCorporateForm = function(){
                $('form.corporate_feedback').submit(function() {
                    var query = $(this).serialize();
                    $.post($(this).attr('action'),query, function(data){
                        obj.html(data);
                        initCorporateForm();
                    })
                    return false;
                });
            };
            initCorporateForm();
        });
        return false;
    });
  
  $('form a.cancel').click(function() {
    $(this).parents('form').get(0).reset();
  });
  
  $('form.main_address_form').submit(function() {
    var form = $(this);
    $.post(form.attr('data-check_url'), form.serialize() , function(data) {
      if (data.ok == "1"){
        form.unbind('submit');
        form.submit();
      } else {
        var obj = new popupConfirm({
          'title' : 'Предупреждение',
          'text' : data.supplier_name + ' не осуществляет доставку на выбранный Вами адрес.<br/>Вы действительньно хотите изменить его?',
          'okLabel' : 'Да',
          'cancelLabel' : 'Отмена',
          'okCallback' : function(){
            form.unbind('submit');
            form.submit();
          }
        });  
      }
    }, "json");
    return false;
  });
})


function formatMoney(n) {
  n = parseFloat(n);
  if (isNaN(n)) {
    n = 0;
  }
  if (n) {
    return parseFloat(n);//.toFixed(2);
  } else {
    return '';
  }
  
  /*
  var cops = ''+parseInt(parseFloat(n)*100);
  return cops.substr(0,cops.length-2)+'.'+cops.substr(cops.length-2);
  */
}

function extend (child, parent) {
  var c = function () {};
  c.prototype = parent.prototype;
  c.prototype.constructor = parent;
  c.prototype.parent = parent;
  return child.prototype = new c;
};

function baseObject(){}
baseObject.prototype.createEventHandler = function(method) {
  var _this = this;
  var f = function(event) {
    return method.call(_this, $(event.currentTarget), event);
  }
  return f;
}
baseObject.prototype.bindFunction = function(f) {
  var _this = this;
  return function(){
    return f.apply(_this, arguments);
  }
}



function popup_form(url, load_callback) {
  $.get(url, function(data) {
    var obj = new popupAjax(data);
    obj.show();

    var onSubmit = function() {
      var loader = new  ajaxLoader();
      $.post(this.action, $(this).serialize(),
        function(data, textStatus, XMLHttpRequest) {
          if (XMLHttpRequest.getResponseHeader('Content-type').search(/json/) !== -1) {
            if (data.location_reload) {
              location.reload(true);
              return false;
            }
            if (data.location) {
              window.location = data.location;
              return false;
            }
          }
          if (data == "1") {
            location.reload(true);
            return false;
          }
          if (data == "close") {
            obj.hide();
            return false;
          }
          obj.html(data);
          bind_forms_submit();
          if (load_callback) {
            load_callback();
          }
          loader.remove();
        }
      );
      return false;
    };
    if (load_callback) {
      load_callback();
    }
    function bind_forms_submit() {
      obj.popup.find('form').each(function() {
        $(this).submit(onSubmit);
      });
      $('.cancel').click(function() {
        obj.hide();
      });
    }
    bind_forms_submit();
  });
}

function popupAjaxNoBodyClick(html, type) {
  this.overlay = $('div.b-overlay');
  this.body = $(document.body);
  this.page = $('div.l-page');
  //this.popup = $('<div class="b-popup"></div>').appendTo(this.body);
  this.popup = $('.b-popup');

  //this.page.append(this.popup);
  this.html(html);

  this.popup.removeClass(popupAjax.type.small);
  this.popup.addClass(type || '');

  this.name = 'popup-' + popupAjax.id++;
  this.scrollbars = this.getScrollbarWidth();
};
//extend(popupAjaxNoBodyClick, popupAjax);
popupAjaxNoBodyClick.prototype.show = function(){  
  this.parent.prototype.show.apply(this);
  this.body.unbind('click.' + this.name);    
}

popupAjaxNoBodyClick.prototype.hide = function(){  
  this.parent.prototype.hide.apply(this);
  this.ajaxClearAttr();    
}

popupAjaxNoBodyClick.prototype.ajaxClearAttr = function() {
  $.ajax({
    'type': 'POST',
    'url': baseUrl + '/homepage/clearAttribute',
    'success': function(data, textStatus, jqXHR) {  
    },
    dataType: 'json'
  });    
}

function popupAjaxForm() {}
extend(popupAjaxForm, baseObject);
popupAjaxForm.prototype.popup = null;
popupAjaxForm.prototype.open = function(url) {
  $.get(url, this.bindFunction(this.showPopup));
};
popupAjaxForm.prototype.showPopup = function(data) {
  this.popup = new popupAjax(data);
  this.popup.show();
  this.init();
}
popupAjaxForm.prototype.init = function(data) {
  this.popup.popup.find('form').submit(this.createEventHandler(this.onFormSubmit));
  this.popup.popup.find('.cancel').click(this.createEventHandler(this.onCancelClick));
}
popupAjaxForm.prototype.responseHtml = function(data, textStatus, jqXHR){
  this.popup.html(data);
  this.init();
}
popupAjaxForm.prototype.responseJson = function(data, textStatus, jqXHR){
  this.popup.hide();
}
popupAjaxForm.prototype.onFormSubmit = function(form) {
  var _this = this;
  $.post(form.attr('action'), form.serialize(),
    function(data, textStatus, jqXHR) {
      if (jqXHR.getResponseHeader('Content-type').search(/json/) !== -1) {
        _this.responseJson(data, textStatus, jqXHR);
      } else {
        _this.responseHtml(data, textStatus, jqXHR);
      }
    }
  );
  return false;
}
popupAjaxForm.prototype.onCancelClick = function(obj) {
  this.popup.hide();
  return false;
}

function ajaxForm(body) {
  this.body = body;
  this.init();
}
extend(ajaxForm, baseObject);
ajaxForm.prototype.body = null;
ajaxForm.prototype.getForm = function(){
  if (this.body[0].tagName.toLowerCase()=='form') {
    return this.body;
  } else {
    return this.body.find('form');
  }
}
ajaxForm.prototype.init = function() {
  var myForm = this.getForm();
    myForm.submit(this.createEventHandler(this.onFormSubmit));
}

ajaxForm.prototype.load = function(url) {
  $.get(url, this.bindFunction(this.render));
};
ajaxForm.prototype.render = function(html) {
  var newBody = $(html);
  this.body.replaceWith(newBody);
  this.body = newBody;
  this.init();
}
ajaxForm.prototype.responseHtml = function(data, textStatus, jqXHR){
  this.render(data);
}
ajaxForm.prototype.responseJson = function(data, textStatus, jqXHR){
    var _this = this;
  if (typeof(data.location) != 'undefined') {
    window.location = data.location;
  }else if(typeof(data.html) != 'undefined'){
        var suppliers_select = $('.suppliers_select'); 
        //$('.b-popup_auth').toggle();
        if ($('.b-popup_auth:last').find('.b-popup-close').length)$('.b-popup_auth:last').find('.b-popup-close').click();
        else $('.b-popup_auth:last').toggle(); 
        ajFSS = new ajaxFormSupplierSelect(suppliers_select);
        ajFSS.render(data.html);        
    }
}
ajaxForm.prototype.onFormSubmit = function(form) {
  var _this = this;
  $.ajax({
    'type': 'POST',
    'url': form.attr('action'),
    'data': form.serialize(),
    'success': function(data, textStatus, jqXHR) {
      if (jqXHR.getResponseHeader('Content-type').search(/json/) !== -1) {
        _this.responseJson(data, textStatus, jqXHR);
      } else {
        _this.responseHtml(data, textStatus, jqXHR);
      }
    },
    'loaderTarget' : this.body
  });
  return false;
}
   
function ajaxFormSupplierSelect(body) {
    this.body = body;
    this.opened = null; 
    this.document_body = $(document.body); 
    this.popup = $(body).parent();
    this.name = this.popup[0].className;
    //this.init(); 
} 
extend(ajaxFormSupplierSelect, baseObject);
ajaxFormSupplierSelect.prototype.init = function(){
    var _this = this;
   //_this.toggle(); 
   if (typeof(this.handler) != 'undefined')
   this.handler.click(function(){
        _this.toggle();
        return false;
    }); 
    else  _this.toggle(); 
    this.popup.find('.b-popup-close').click(function(){
        //$('.b-supplier_switch').hide();
        _this.toggle();
        return false;
    });
    this.popup.bind('click.' + this.name, function(e){
        e.stopPropagation();
    });
    _this.popup.find('select').change(this.createEventHandler(this.changeValue));  //this.createEventHandler(this.changeValue)     
    _this.popup.find('form').submit(this.createEventHandler(this.onFormSubmit)); 
    if(_this.popup.find('optgroup').length > 1) _this.popup.find('select').attr('size', _this.popup.find('select').attr('size') + _this.popup.find('optgroup').length - 1);
}
ajaxFormSupplierSelect.prototype.onFormSubmit = function(){
    if ($('.userDescription').length){
        window.location.reload(true);
        return false;
    };         
}
ajaxFormSupplierSelect.prototype.changeValue = function(){
    var _this = this;
    var request = {            
        'supplier_id' : this.popup.find('select').val(),
    };
    $.ajax({
            'type': 'POST',
            'url': baseUrl + 'homepage/selectCurrentSupplier',
            'data': request,
            'dataType': 'json',  
            'success': function(data){
                if (typeof(data.sup_name) != "undefined") {
                    _this.popup.find('form div:eq(0) .sup_name').text(data.sup_name); 
                    _this.popup.find('form div:eq(0) .sup_address').text(data.sup_address); 
                } 
            },
            'showLoader' : false
        });
}
ajaxFormSupplierSelect.prototype.toggle = function(){
    var _this = this;
    if (_this.popup.is(':hidden') || $('.userDescription').length){
        if (!_this.opened) {
            _this.document_body.bind('click.' + _this.name, function(){
                _this.toggle();
            });
        } else {
            _this.document_body.unbind('click.' + _this.name);
        }

        _this.opened = !_this.opened;
        _this.popup.toggle();
    }
}
ajaxFormSupplierSelect.prototype.render = function(html) {
    var newBody = $(html);
    this.body.replaceWith(newBody);
    //this.body = newBody;
    this.init();
}
$(function(){
    var signinFormWrapper = $('.signinFormWrapper:last');
    if(signinFormWrapper.length) {
      new ajaxForm(signinFormWrapper);
    }
    var ajaxFSS = $('.b-supplier_switch');
    if(ajaxFSS.length === 1) {
        ajFSS = new ajaxFormSupplierSelect($('.suppliers_select'));
        ajFSS.handler = $('a.supplier_switch');
        ajFSS.init();
        return false;
    }       
});


/**
 * Determines if a form is dirty by comparing the current value of each element
 * with its default value.
 *
 * @param {Form} form the form to be checked.
 * @return {Boolean} true if the form is dirty, false otherwise.
 */
function formIsDirty(form)
{
    for (var i = 0; i < form.elements.length; i++)
    {
        var element = form.elements[i];
        var type = element.type;
        if (type == "checkbox" || type == "radio")
        {
            if (element.checked != element.defaultChecked)
            {
                return true;
            }
        }
        else if (type == "hidden" || type == "password" || type == "text" ||
                 type == "textarea")
        {
            if (element.value != element.defaultValue)
            {
                return true;
            }
        }
        else if (type == "select-one" || type == "select-multiple")
        {
            for (var j = 0; j < element.options.length; j++)
            {
                if (element.options[j].selected !=
                    element.options[j].defaultSelected)
                {
                    return true;
                }
            }
        }
    }
    return false;
}


window.pageParams = {};

function setPageParam(name, value){
  if (typeof(window.pageParams) == 'undefined') {
    window.pageParams = {};
  }
  window.pageParams[name] = value;
}

function getPageParam(name, defaultValue) {
  if (typeof(window.pageParams[name]) != 'undefined') {
    return window.pageParams[name];
  } else {
    if (!defaultValue) {
      defaultValue = null;
    }
    return defaultValue;
  }
}

function ajaxError(error) {
  alert('Ошибка сапроса к серверу');
  console.log(error);
}

function addressSearchPopup(body, label_handler, example_handler) {   
    this.body = body;        
    this.label_handler = label_handler;
    this.example_handler = example_handler;
}
extend(addressSearchPopup, baseObject);
addressSearchPopup.prototype.init = function(){
    this.body.mouseenter(this.createEventHandler(this.onMouse));     
    this.body.mouseleave(this.createEventHandler(this.onMouseLeave));        
}
addressSearchPopup.prototype.onMouse = function(){
    this.label_handler.hide();
    this.example_handler.show(); 
    //$('.supplierSearch_address_example').parent().css('text-align', 'center');   
}
addressSearchPopup.prototype.onMouseLeave = function(){
    this.example_handler.hide();
    this.label_handler.show(); 
    //$('.supplierSearch_address_example').parent().css('text-align', 'left');         
}

$(document).ready(function() {
  if ($('.convert_bonuses_form').length) initConvertBonuses();
  function initConvertBonuses()
  {
    $('.convert_bonuses_form').ajaxForm(function(data){
      if (typeof(data) !== 'undefined' && data){
        $('.convert_bonuses').html(data);
        if (!$('.convert_bonuses_form .error_list').length) window.location.href=window.location.href;
        initConvertBonuses();
      }        
    });
  }
  if ($('.convert_billing_form').length) initConvertBilling();
  function initConvertBilling()
  {
    $('.convert_billing_form').ajaxForm(function(data){
      if (typeof(data) !== 'undefined' && data){
        $('.convert_billing').html(data);
        //if (!$('.convert_billing_form .error_list').length) window.location.href=window.location.href;
        initConvertBilling();
      }        
    });
  }
});