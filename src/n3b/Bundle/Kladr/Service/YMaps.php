<?php

namespace n3b\Bundle\Kladr\Service;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use n3b\Bundle\Util\String;

class YMaps
{

    use ContainerAwareTrait;

    public function __construct(ContainerInterface $containerInterface){
        $this->container = $containerInterface;
    }

	/**
	 * @return YMapsService
	 */
	static function getInstance(ContainerInterface $containerInterface) {
		static $i;
		if (!isset($i)) {
			$i = new YMapsService($containerInterface);
		}
		return $i;
	}

	function getCoordsFromCityIdAndAddressInfo($cityId, AddressInfo $addressInfo) {
		$city = AddressGroupTable::getInstance()->findOneById($cityId);
		$fullAddress = $city->getName() . ', ' . $addressInfo['street']['prefix'] . ' ' . $addressInfo['street']['name'] . ', ' . $addressInfo['house']['number'];
		return $this->getCoordsFromAddress($fullAddress);
	}

	function getCoordsFromAddress($address, $prec= array()) {

		/*$cache = YMapsAddressCacheTable::getInstance()->findOneByAddress($address);
		if ($cache) {
			return array(
				'lng' => $cache->getLng(),
				'lat' => $cache->getLat(),
			);
		}*/

		$url = "http://geocode-maps.yandex.ru/1.x/?geocode=" . str_replace(' ', '+', $address) . "&key={$this->getKey()}&format=json";

		$fileContent = file_get_contents($url);
		if (!$fileContent) {
			throw new sfException('Can not get content of file ' . $url);
		}

		$data = json_decode($fileContent, true);

		if ($data === null) {
			throw new sfException('Can not decode json data from url: ' . $url);
		}

		if (isset($data['error'])) {
			throw new sfException('Ymaps error:' . $data['error']['message']);
		}

		$featureMember = $data['response']['GeoObjectCollection']['featureMember'];  
        $precision= $featureMember[0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['precision'];

		if (
			isset($featureMember[0]['GeoObject']['Point']['pos'])
			&& in_array($precision, array_merge(array('exact', 'number', 'near'), $prec))
		) {
			$pos = $featureMember[0]['GeoObject']['Point']['pos'];
			$pos = explode(' ', $pos);
			/*$cache = new YMapsAddressCache();
			$cache->setAddress($address);
			$cache->setLng($pos[0]);
			$cache->setLat($pos[1]);
			$cache->save();*/
			return array(
				'lng' => $pos[0],
				'lat' => $pos[1],
			);
		} else {
			return false;
		}
	}

    public function moscowFilter($street_name, $street_socr, $points)
    {
        $repository = $this->container->get('doctrine')
            ->getRepository('n3bKladrBundle:MoscowStreet');

        $query = $repository->createQueryBuilder('ms');

        if($street_name)
        {
            $query->andWhere('ms.title LIKE :street_name')->setParameter('street_name', '%' . $street_name . '%');
        }

        if($street_socr && count($street_socr))
        {
            $impl_str_socr = $this->implodeStrAr($street_socr);
            $query->andWhere("ms.socr IN (" . $impl_str_socr . ")");
        }

        if($points)
        {
            $bounds = $this->getPoligonBounds($points);

            $query->andWhere('ms.lng between :min_lng and :max_lng')
                    ->andWhere('ms.lat between :min_lat and :max_lat')
                    ->setParameter('min_lng', $bounds['min_lng'])
                    ->setParameter('max_lng', $bounds['max_lng'])
                    ->setParameter('min_lat', $bounds['min_lat'])
                    ->setParameter('max_lat', $bounds['max_lat']);
        }
        $query->orderBy('ms.title');
//var_dump($query->getQuery()->getParameters()->toArray());
        $results = $query->getQuery()->getResult();

        if($points)
        {
            foreach($results as $i => $result):
                if(!$this->pointInPolygon(array($result->getLng(), $result->getLat()), $points))
                {
                    unset($results[$i]);
                }
            endforeach;
        }

        return $results;
    }

    public function peterFilter($street_name, $street_socr, $points)
    {
        $repository = $this->container->get('doctrine')
            ->getRepository('n3bKladrBundle:PeterStreet');

        $query = $repository->createQueryBuilder('ms');

        if($street_name)
        {
            $query->andWhere('ms.title LIKE :street_name')->setParameter('street_name', '%' . $street_name . '%');
        }

        if($street_socr && count($street_socr))
        {
            $impl_str_socr = $this->implodeStrAr($street_socr);
            $query->andWhere("ms.socr IN (" . $impl_str_socr . ")");
        }

        if($points)
        {
            $bounds = $this->getPoligonBounds($points);

            $query->andWhere('ms.lng between :min_lng and :max_lng')
                ->andWhere('ms.lat between :min_lat and :max_lat')
                ->setParameter('min_lng', $bounds['min_lng'])
                ->setParameter('max_lng', $bounds['max_lng'])
                ->setParameter('min_lat', $bounds['min_lat'])
                ->setParameter('max_lat', $bounds['max_lat']);
        }
        $query->orderBy('ms.title');
//var_dump($query->getQuery()->getParameters()->toArray());
        $results = $query->getQuery()->getResult();

        if($points)
        {
            foreach($results as $i => $result):
                if(!$this->pointInPolygon(array($result->getLng(), $result->getLat()), $points))
                {
                    unset($results[$i]);
                }
            endforeach;
        }

        return $results;
    }

    function implodeStrAr($str_ar)
    {
        $res = '';

        foreach($str_ar as $str):
            $res.=  "'" . $str . "'" . ',';
        endforeach;

        $res = strlen($res) ? substr($res, 0, strlen($res) - 1) : $res;

        return $res;
    }

    function getPoligonBounds($points){
        if (!is_array($points) || !$points) {
            return array(
                'min_lng' => 0,
                'min_lat' => 0,
                'max_lng' => 0,
                'max_lat' => 0,
            );
        }

        $lngs = array();
        $lats = array();
        foreach ($points as $point) {
            $lngs[] = $point[0];
            $lats[] = $point[1];
        }
        return array(
            'min_lng' => min($lngs),
            'min_lat' => min($lats),
            'max_lng' => max($lngs),
            'max_lat' => max($lats),
        );
    }

	function getKey() {
		/*
		 * @var sfWebRequest
		 */
		/*$request = sfContext::getInstance()->getRequest();

		if ($host = $request->getHost()) {
			$pos = strpos($host, ':');
			if ($pos !== false) {
				$host = substr($host, 0, $pos);
			}
			if (substr($host, 0,4) == 'www.') {
				$host = substr($host,4);
			}
			if (strpos($request->getPathInfo(),'/minisite') === 0) {
				$pos = strpos($host, '.');
				$host = substr($host, $pos+1);
			}
		} else {
			$host = 'default';
		}*/

		return $this->container->getParameter('ymaps_key');
	}

	function getApiUrl() {
		return "http://api-maps.yandex.ru/1.1/index.xml?key={$this->getKey()}";
	}

	function distance($lng1, $lat1, $lng2, $lat2, $miles = false) {  
		$pi80 = M_PI / 180;
		$lat1 *= $pi80;
		$lng1 *= $pi80;
		$lat2 *= $pi80;
		$lng2 *= $pi80;

		$r = 6372.797; // mean radius of Earth in km
		$dlat = $lat2 - $lat1;
		$dlng = $lng2 - $lng1;
		$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);     
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));  
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));   
		$km = $r * $c;  
       // var_dump('$km ' . $km);
		return ($miles ? ($km * 0.621371192) : $km);
	}

	function getStaticMapByCityIdAndAddress($cityId, $address) {
		$city = AddressGroupTable::getInstance()->findOneById($cityId);
		return $this->getStaticMapByAddress($city->getName() . ', ' . $address);
	}

	function getStaticMapByAddress($address) {
		$coords = $this->getCoordsFromAddress($address);
		$coords = $coords['lng'].','.$coords['lat'];
		return 'http://static-maps.yandex.ru/1.x/?ll='.$coords.'&pt='.$coords.',pmntl&z=15&l=map&size=450,400&key='.$this->getKey();
	}
    function getStaticMapByAddressSize($address, $width, $height) {
        $coords = $this->getCoordsFromAddress($address);
        if ($coords) {
            $coords = $coords['lng'].','.$coords['lat'];
            return 'http://static-maps.yandex.ru/1.x/?ll='.$coords.'&pt='.$coords.',pmntl&z=15&l=map&size='.$width.','.$height.'&key='.$this->getKey();
        } else {
            return '/img/addressNotFound.png';
        }
    }

	/*
	 * calculate top, bottom, left and right bounds for circle
	 */
	function getBoundsForCircle($centerCoords, $radius){
		$R = 6378.1;
		$dLat = 90 / ($R * M_PI / 2 / $radius);
		$dLng = 180 * ($radius / (cos(M_PI/180*$centerCoords['lat'])*$R*M_PI));
		return array(
			'min_lng' => $centerCoords['lng'] - $dLng,
			'min_lat' => $centerCoords['lat'] - $dLat,
			'max_lng' => $centerCoords['lng'] + $dLng,
			'max_lat' => $centerCoords['lat'] + $dLat,
		);
	}

    function pointInPolygon($point, $polygon, $pointOnVertex = true)
    {
        $this->pointOnVertex = $pointOnVertex;

        // Transform string coordinates into arrays with x and y values
        $point = $this->preparePoint($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = $this->preparePoint($vertex);
        }

        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i = 1; $i <= $vertices_count; $i++) {
            $vertex1 = $vertices[$i - 1];
            if ($i == $vertices_count) $vertex2 = $vertices[0];
            else $vertex2 = $vertices[$i];

            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }

            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is even, then it's in the polygon.
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }

    function pointOnVertex($point, $vertices)
    {
        foreach ($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
    }

    function preparePoint($point)
    {
        return array("x" => $point[0], "y" => $point[1]);
    }
}
