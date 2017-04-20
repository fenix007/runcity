// Оверлей "Круг"
//
// center - географические координаты центра
// radius - радиус круга в км
// options.accuracy - количество граней многоугольника
function Circle2 (center, radius, options) {
	this.center = center;
	this.radius = radius;
	this.options = YMaps.jQuery.extend({accuracy : 360}, options);

	// Вызывает родительский конструктор
	YMaps.Polygon.call(this, [], this.options);

	// Вызывается при добавлении круга на карту
	this.onAddToMap = function (map, container) {
		this.map = map;
		this.calculatePoints();

		YMaps.Polygon.prototype.onAddToMap.call(this, map, container);
	}

	// Устанавливает новый центр и радиус
	this.setCenter = function (newCenter, newRadius) {
		if (this.map && (!this.center.equals(newCenter) || this.radius != newRadius)) {
			this.center = newCenter;
			this.radius = newRadius || this.radius;
			this.calculatePoints();
		}
	}

	// Устанавливает новый радиус
	this.setRadius = function (newRadius) {
		if (this.map) {
			this.radius = newRadius;
			this.calculatePoints();
		}
	}

	// Вычисляет точки окружности
	this.calculatePoints = function () {

			// Откладываем геоточку от центра к северу на заданном расстоянии
		var northPoint = new YMaps.GeoPoint(
							this.center.getLng(),
							this.center.getLat() + this.radius / 112.2
						),

			// Пиксельные координаты на последнем масштабе
			pixCenter = this.map.coordSystem.fromCoordPoint(this.center),

			// Радиус круга в пикселях
			pixRadius = pixCenter.getY() - this.map.coordSystem.fromCoordPoint(northPoint).getY(),

			// Вершины многоугол
			points = [],

			// Вспомогательные переменные
			twoPI = 2 * Math.PI,
			delta = twoPI / this.options.accuracy;

		for (var alpha = 0; alpha < twoPI; alpha += delta) {
			points.push(
				this.map.coordSystem.toCoordPoint(
					new YMaps.Point(
						Math.cos(alpha) * pixRadius + pixCenter.getX(),
						Math.sin(alpha) * pixRadius + pixCenter.getY()
					)
				)
			)
		}

		this.setPoints(points);
	}
}

extend(Circle2, YMaps.Polygon);