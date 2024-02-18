function radiusRow(body){
	this.body = body;
	this.init();
}
extend(radiusRow, baseObject);
radiusRow.prototype.body = null;
radiusRow.prototype.map = null;
radiusRow.prototype.circle = null;
radiusRow.prototype.radius = null;
radiusRow.prototype.supplierMark = null;
radiusRow.prototype.polygon = null;
radiusRow.prototype.isOpen = false;
radiusRow.prototype.street_points =  [];
radiusRow.prototype.getDefaultName = function(){
	var index = $('.b-admin-radius__section').index(this.body[0]);
	return 'Район ' + (index+1);
};
radiusRow.prototype.init = function(){
	var _this = this;

	if (!this.getTitle()) {
		this.setTitle(this.getDefaultName());
	}

	this.body.find('a.del').click(function(e){
		return _this.onDelLinkClick(e);
	});

	this.getTitleTag().click(function(e){
		return _this.onTitleClick(e);
	});

    this.body.find('.show_street').click(function(e){
        return _this.getPolygonStreet(e);
    });

    this.body.find('.street_name').keyup(function(e){
        if($(this).val().length >= 3)
        {
            _this.getPolygonStreet(e);
        }
    });
    this.body.find('.object_filter').keyup(function(e){
        if($(this).val().length >= 3)
        {
            YMaps.Geocode($(this).val(), {
                /**
                 * Опции запроса
                 * @see http://api.yandex.ru/maps/doc/jsapi/2.1/ref/reference/geocode.xml
                 */
                 boundedBy: _this.map.getBounds(),// Сортировка результатов от центра окна карты
                 strictBounds: true // Вместе с опцией boundedBy будет искать строго внутри области, указанной в boundedBy
                //results: 1 // Если нужен только один результат, экономим трафик пользователей
            }).then(function (res) {
                    // Выбираем первый результат геокодирования.
                    var firstGeoObject = res.geoObjects.get(0),
                    // Координаты геообъекта.
                        coords = firstGeoObject.geometry.getCoordinates(),
                    // Область видимости геообъекта.
                        bounds = firstGeoObject.properties.get('boundedBy');

                    // Добавляем первый найденный геообъект на карту.
                    myMap.geoObjects.add(firstGeoObject);
                    // Масштабируем карту на область видимости геообъекта.
                    myMap.setBounds(bounds, {
                        checkZoomRange: true // проверяем наличие тайлов на данном масштабе.
                    });
                });
            //_this.getPolygonStreet(e);
        }
    });
    this.body.find('.street_socr').change(function(e){
        _this.getPolygonStreet(e);
    });
    this.body.find('.street_polygon').click(function(e){
        _this.getPolygonStreet(e);
    });

};
radiusRow.prototype.getPolygonStreet = function(e){
    _this = this;
    var street_name     = this.body.find('.street_name').val();
    var street_socr     = this.body.find('.street_socr').val();
    var street_points   = this.body.find('.polygon-points').val();
    var points_check    = this.body.find('.street_polygon').is(':checked');
    var object_filter   = this.body.find('.object_filter').val();
    $.ajax({
        'type': 'POST',
        'url': baseUrl + filterUrl,
        'data': {
            'street_name'   : street_name,
            'street_socr'   : street_socr,
            'points_check'  : points_check,
            'street_points' : street_points
        },
        'success': function($data)
        {
            $('.street_res').html($data);
            $('.street_item').unbind('mouseenter.my_hover');
            $('.street_item').bind('mouseenter.my_hover', function(el){
                lng = $(this).attr('data-lng');
                lat = $(this).attr('data-lat');
                _this.hoverStreet(lng, lat);
            });
            $('.street_item').unbind('mouseout.my_hover');
            $('.street_item').bind('mouseout.my_hover', function(el){
                _this.clearStreet();
            });
        }
    });

}
radiusRow.prototype.onDelLinkClick = function(e){
	var el = $(e.currentTarget);
	cabinet.deleteObject('deliveryRadius', this.getId(), this.body);
	return false;
}
radiusRow.prototype.closeForm = function(){
	this.getFormContainer().hide();
	this.getTitleTag().find('i').removeClass('b-icon_toggle_act');
	this.showHeaderInfo(true);
}
radiusRow.prototype.showForm = function(){
	this.getFormContainer().show();
	this.getTitleTag().find('i').addClass('b-icon_toggle_act');
}
radiusRow.prototype.onCancelClick = function(e) {
	this.closeForm();
}
radiusRow.prototype.updateFromResponseData = function(data) {
	this.setTitle(data.name);
	this.setPrice(data.shipping_price);
	this.setId(data.id);
	this.getHeaderInfo().find('.min-amount').text(data.min_amount);
	this.getHeaderInfo().find('.delivery-time').text(data.delivery_time);
}
radiusRow.prototype.getTitle = function(){
	return $.trim(this.getTitleTag().find('.text').text());
}
radiusRow.prototype.setTitle = function(title) {
	if (!title) {
		title = this.getDefaultName();
	}
	this.getTitleTag().find('.text').text(title);
}
radiusRow.prototype.setPrice = function(price) {
	this.body.find('span.price>span').text(price);
}
radiusRow.prototype.setId = function(id) {
	this.body.data('id', id);
}
radiusRow.prototype.updateBounds = function(){
	var polygon;
	if (this.getTypeSelect().val() == 'radius') {
		polygon = this.circle;
	} else {
		polygon = this.polygon;
	}
	var bounds = this.getPolygonBounds(polygon);

	var form = this.getFormContainer();
	form.find('.min_lng').val(bounds.minLng);
	form.find('.min_lat').val(bounds.minLat);
	form.find('.max_lng').val(bounds.maxLng);
	form.find('.max_lat').val(bounds.maxLat);
}
radiusRow.prototype.saveForm = function(callback) {
	var form = this.getFormContainer().find('form');
	$.ajax({
		'type': 'POST',
		'url': form.attr('action'),
		'data': form.serialize(),
		'success': callback
	});
}
radiusRow.prototype.onFormSubmit = function(form){
	//this.updateBounds();
	var _this = this;
	this.saveForm(function(data, textStatus, jqXHR){
		if (isJsonResponse(jqXHR)) {
			_this.updateFromResponseData(data);
			_this.closeForm();
			return false;
		} else {
			form.replaceWith($(data));
			_this.initForm();
		}
	});
	return false;
};
radiusRow.prototype.onShowAllLinkClick = function(link){
	var addressesList = this.getFormContainer().find('.addresses-list');
	$.ajax({
		'type': 'POST',
		'url': baseUrl+'/cabinet/getDeliveryAddressesByRadius',
		'data': {'id':this.body.data('id')},
		'success': function(data){
			addressesList.text(data);
		},
		'loaderTarget' : addressesList
	});
	return false;
}

radiusRow.prototype.initForm = function(){
	var _this = this;
	/*$(this.getFormContainer().find('.prices input').get(0)).click(function(){
		_this.getFormContainer().find('.min-amount input').focus().select();
	});

	var nameInput = this.getFormContainer().find('[name$=\\[name\\]]');
	nameInput.attr('placeholder', this.getDefaultName());
	//addPlaceholderBehaviour(nameInput);

	this.body.find('.show-all').click(this.createEventHandler(this.onShowAllLinkClick));

	this.showHeaderInfo(false);
	this.getFormContainer().find('a.cancel').click(function(e){
		return _this.onCancelClick(e);
	});
	this.getFormContainer().find('.b-input-number').each(function(){
		new inputNumber($(this));
	});
	this.getFormContainer().find('form').submit(this.createEventHandler(this.onFormSubmit));
	*/

	this.map = new YMaps.Map(this.body.find('.map'));
	var centerPoint = new YMaps.GeoPoint(supplier.longitude, supplier.latitude);
	this.map.setCenter(centerPoint, 11);

	this.map.addControl(new YMaps.SmallZoom());
	this.map.addControl(new YMaps.ToolBar());

	this.body.find('.b-input-number .b-icon_minus, .b-input-number .b-icon_plus').click(function(e){
		_this.onRadiusChange();
	})
	this.getMinAmountInput().keyup(function(){
		_this.onMinAmountChange();
	});
	this.getTypeSelect().change(function(){
		_this.onTypeChange();
	});

	this.onTypeChange();
	this.showSupplierMark();
	this.getFormContainer().find('.edit-addresses-link').click(this.createEventHandler(this.onEditAddressesLinkClick));

    this.map.setZoom(13, {'smooth':false});
}
radiusRow.prototype.hoverStreet = function(lng, lat){
    this.clearStreet();
    this.street_points = new YMaps.Placemark(new YMaps.GeoPoint(lng, lat));
    this.map.addOverlay(this.street_points);
}
radiusRow.prototype.clearStreet = function(){
    this.map.removeOverlay(this.street_points);
}
radiusRow.prototype.getForm = function(){
	return this.getFormContainer().find('form');
}
radiusRow.prototype.onEditAddressesLinkClick = function(link){
	var _this = this;
	var goToAddresses = function(id){
		new ajaxLoader();
		window.location = baseUrl + '/cabinet/showDeliveryAddresses/radius/'+id;
	}
	if (!formIsDirty(this.getForm().get(0))) {
		goToAddresses(this.body.data('id'));
		return false;
	}
	var form = this.getForm();
	this.saveForm(function(data, textStatus, jqXHR){
		if (isJsonResponse(jqXHR)) {
			goToAddresses(data.id);
			return false;
		} else {
			form.replaceWith($(data));
			_this.initForm();
		}
	});

	return false;
};
radiusRow.prototype.drag = function(point) {
	var points = this.polygon.getPoints();
	var deltaLng = point.getGeoPoint().getLng() - supplier.longitude;
	var deltaLat = point.getGeoPoint().getLat() - supplier.latitude;
	for (var i = 0; i < points.length; i++) {
		points[i].setLng(points[i].getLng() + deltaLng);
		points[i].setLat(points[i].getLat() + deltaLat);
	}
	supplier.longitude = point.getGeoPoint().getLng();
	supplier.latitude  = point.getGeoPoint().getLat();
	this.polygon.setPoints(points);
};
radiusRow.prototype.showSupplierMark = function(){
	var _this = this;
	this.supplierMark = new YMaps.Placemark(new YMaps.GeoPoint(supplier.longitude, supplier.latitude), {
		style : {
			iconStyle : {
				href : "/img/admin_address_head.png",
				size : new YMaps.Point(18, 18),
				offset : new YMaps.Point(-9, -9)
			}
		},
		draggable : true,
		hasBalloon : false
		//hasHint : true
	});
	YMaps.Events.observe(this.supplierMark, this.supplierMark.Events.Drag, function (obj) {
		_this.drag(obj);
	});
	this.map.addOverlay(this.supplierMark);
}
radiusRow.prototype.showRadius = function(){
	var centerPoint = new YMaps.GeoPoint(supplier.longitude, supplier.latitude);
    this.circle = new Circle2(centerPoint, 0,{
        style : {
            polygonStyle : {
                outline : true,
                strokeWidth : 3,
                strokeColor : "00336655",
                fillColor : "00999933"
            }
        },
        interactive : YMaps.Interactivity.NONE
    });
    this.map.addOverlay(this.circle);
	this.map.setCenter(centerPoint);
	this.setRadius(10);
}
radiusRow.prototype.hideRadius = function(){
	this.map.removeOverlay(this.circle);
	//this.map.removeOverlay(this.supplierMark);
}
radiusRow.prototype.showPolygon = function(){
	var _this = this;
	// Создание стиля для многоугольника
	var style = new YMaps.Style("default#greenPoint");
	style.polygonStyle = new YMaps.PolygonStyle();
	style.polygonStyle.fill = 1;
	style.polygonStyle.outline = 1;
	style.polygonStyle.strokeWidth = 3;
	style.polygonStyle.strokeColor = "00336655";
	style.polygonStyle.fillColor = "00999933";
	YMaps.Styles.add("polygon#Example", style);

	var points = $.parseJSON(this.getPolygonPointsInput().val());
	if (points == 'null') {
		points = null;
	}

	if (points) {
		for (var i=0; i < points.length; i++) {
			points[i] = new YMaps.GeoPoint(points[i][0], points[i][1]);
		}
	} else {
		var distance = 0.02;
		points = [
		new YMaps.GeoPoint(parseFloat(supplier.longitude)-distance, parseFloat(supplier.latitude)-distance/1.5),
		new YMaps.GeoPoint(parseFloat(supplier.longitude)-distance, parseFloat(supplier.latitude)+distance/1.5),
		new YMaps.GeoPoint(parseFloat(supplier.longitude)+distance, parseFloat(supplier.latitude)+distance/1.5),
		new YMaps.GeoPoint(parseFloat(supplier.longitude)+distance, parseFloat(supplier.latitude)-distance/1.5)
		];
	}
console.log(points);
	// Создание многоугольника и добавление его на карту
	this.polygon = new YMaps.Polygon(points, {style: "polygon#Example", 'draggable': true});
	this.map.addOverlay(this.polygon);
	this.polygon.startEditing();

	this.map.setBounds(this.getPolygonGeoBounds(this.polygon));

	YMaps.Events.observe(this.polygon, this.polygon.Events.PositionChange, function () {
		_this.onPolygonPositionChange();
	});
	
}
radiusRow.prototype.getPolygonGeoBounds = function(polygon) {
	var bounds = this.getPolygonBounds(polygon);
	return new YMaps.GeoBounds(
		new YMaps.GeoPoint(bounds.minLng, bounds.minLat),
		new YMaps.GeoPoint(bounds.maxLng, bounds.maxLat)
	);
}
radiusRow.prototype.getPolygonBounds = function(polygon) {
	var points = polygon.getPoints();
	var lngs=[], lats=[];
	$.each(points, function(){
		lngs.push(this.getLng());
		lats.push(this.getLat());
	});
	return {
		minLng :  Math.min.apply(Math, lngs),
		maxLng :  Math.max.apply(Math, lngs),
		minLat :  Math.min.apply(Math, lats),
		maxLat :  Math.max.apply(Math, lats)
	};
}
radiusRow.prototype.getPolygonPointsInput = function(){
	return this.body.find('.polygon-points');
}
radiusRow.prototype.onPolygonPositionChange = function(){
	var points = this.polygon.getPoints();
	var point;
	var jsonParts = [];
	while (point = points.shift()) {
		jsonParts.push('['+point.getLng()+','+point.getLat()+']');
	}
	this.getPolygonPointsInput().val('['+jsonParts.join(',')+']');
    if(this.body.find('.street_polygon').is(':checked'))
    {
        this.getPolygonStreet(false);
    }
}
radiusRow.prototype.hidePolygon = function(){
	this.map.removeOverlay(this.polygon);
}
radiusRow.prototype.getTypeSelect = function(){
	return this.getFormContainer().find('#supplier_delivery_radius_type');
}
radiusRow.prototype.getRadius = function(){
	return this.getFormContainer().find('.radius');
}
radiusRow.prototype.onTypeChange = function(){
	var addressesContainer = this.getFormContainer().find('.addresses');
	var mapContainer = this.getFormContainer().find('.map');
	if (this.getTypeSelect().val() == 'addresses') {
		addressesContainer.show();
		mapContainer.hide();
		this.getRadius().hide();
	} else {
		addressesContainer.hide();
		mapContainer.show();
		if (this.getTypeSelect().val()=='radius') {
			this.getRadius().show();
			this.showRadius();
			this.hidePolygon();
		} else {
			this.getRadius().hide();
			this.hideRadius();
			this.showPolygon();
		}
	}

}
radiusRow.prototype.onMinAmountChange = function(){
	this.getFormContainer().find('.prices input').first().val(this.getMinAmountInput().val());
};
radiusRow.prototype.getMinAmountInput = function(){
	return this.getFormContainer().find('.min-amount input');
};

radiusRow.prototype.onRadiusChange = function(){
	this.setRadius(1);
}

radiusRow.prototype.setRadius = function(radius) {
    this.circle.setRadius(radius);
	var zoom;
	if (radius > 70) {
		zoom = 8
	} else if (radius > 30) {
		zoom = 9
	} else if (radius > 15) {
		zoom = 10
	} else if (radius > 8) {
		zoom = 11
	} else {
		zoom = 12
	}
	this.map.setZoom(zoom, {'smooth':true});
}

radiusRow.prototype.getTitleTag = function(){
	return this.body.find('h3.title');
}
radiusRow.prototype.getFormContainer = function(){
	return this.body.find('.b-admin-radius__section__body');
}
radiusRow.prototype.getHeaderInfo = function(){
	return this.body.find('.b-admin-radius__section__head .delivery, .b-admin-radius__section__head h3.title .text');
}
radiusRow.prototype.showHeaderInfo = function(show){
	var headerInfo = this.getHeaderInfo();
	if (show) {
		headerInfo.show();
	} else {
		headerInfo.hide();
	}
}
radiusRow.prototype.getId = function(){
	return this.body.data('id');
}


function radiusList(body){
	this.body = body;
	this.init();
}
extend(radiusList, baseObject);
radiusList.prototype.body = null;
radiusList.prototype.getRadiusToOpen = function(){
	var execResult = /openRadius([0-9]+)/.exec(window.location.hash);
	if (execResult) {
		return execResult[1];
	} else {
		return null;
	}
}
radiusList.prototype.init = function(){

	var radiusToOpen = this.getRadiusToOpen();
	
	this.body.find('.b-admin-radius__section').each(function(){
		var radius = new radiusRow($(this));
		if ($(this).data('id') == radiusToOpen) {
			$(this).find('.b-icon_toggle').addClass('b-icon_toggle_act');
		}
        radius.initForm(this.body);
	});

	this.body.find('a.b-btn_plus').click(this.createEventHandler(this.onPlusClick));
}
radiusList.prototype.onPlusClick = function(link){
	var radiusBody = $('.hiddenRadiusRow').children('div').clone();
	//radiusBody.insertBefore(link.parent('div'));
	radiusBody.appendTo(this.body.find('.b-admin-radius__body'));
//	radiusBody.find('.title').click(function(){
//		var $this = $(this);
//		$this.children('i').toggleClass('b-icon_toggle_act');
//		$this.closest('div.b-admin-radius__section').children('div.b-admin-radius__section__body').toggle();
//	});
	var row = new radiusRow(radiusBody);
    row.showForm();
    row.loadForm();
	return false;
}

$(function(){

	new radiusList($('.b-admin-radius'));

});
