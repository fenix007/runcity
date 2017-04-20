$(function(){

	$('.ymap').each(function(){

            // Создание экземпляра карты и его привязка к созданному контейнеру
            var map = new YMaps.Map(this);
			map.addControl(new YMaps.SmallZoom());
			map.addControl(new YMaps.ToolBar());
			var container = $(this);
			var center = new YMaps.GeoPoint(container.data('lng'), container.data('lat'));
            map.setCenter(center, 15);
			var placemark = new YMaps.Placemark(center, {'draggable': true});
			placemark.name = container.data('name');
			placemark.description = container.data('description');
            //map.addOverlay(placemark);

	})          

});