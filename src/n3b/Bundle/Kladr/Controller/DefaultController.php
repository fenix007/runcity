<?php

/**
 * Created by PhpStorm.
 * User: alex
 * Date: 02.04.14
 * Time: 01:11
 */

namespace n3b\Bundle\Kladr\Controller;

use Exception;

use n3b\Bundle\Kladr\Service\Street;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{

    /**
     * @Route("/moscow", name="kladr_moscow")
     *
     * @throws NotFoundHttpException
     * @Template()
     */

    public function moscowAction()
    {
        $ymaps_key = $this->container->getParameter('ymaps_key');
        $socr_arr = Street::SOCR;

        return [
            'ymaps_key' => $ymaps_key,
            'socr_arr' => $socr_arr,
            'map_width' => $this->getParameter('map_width'),
            'map_height' => $this->getParameter('map_height')
        ];
    }

    /**
     * @Route("/polygon/street", name="polygon_street")
     *
     * @throws NotFoundHttpException
     * @Template()
     */

    public function getPolygonStreetAction(Request $request)
    {
        $street_points = $request->request->get('street_points');
        $points_check = $request->request->get('points_check') !== 'false' ? true : false;
        $street_name = $request->request->get('street_name');
        $street_house = $request->request->get('street_house');
        $object_filter = $request->request->get('object_filter');
        $street_socr = $request->request->get('street_socr') !== 'null' ? $request->request->get('street_socr') : false;

        $pp_ar = array();
        if ($street_points && $points_check) {
            $pp_ar = json_decode($street_points);
        }

        if (count($street_socr) === 1 && !strlen($street_socr[0])) {
            $street_socr = false;
        }

        $YMaps = $this->container->get('ymaps_service');

        $streets = $YMaps->moscowFilter($street_name, $street_socr, $street_house, $pp_ar);

        return array('streets' => $streets);
    }

    /**
     * @Route("/peter", name="kladr_peter")
     *
     * @throws NotFoundHttpException
     * @Template()
     */

    public function peterAction()
    {
        $ymaps_key = $this->container->getParameter('ymaps_key');
        $socr_arr = Street::SOCR;

        return [
            'ymaps_key' => $ymaps_key,
            'socr_arr' => $socr_arr,
            'map_width' => $this->getParameter('map_width'),
            'map_height' => $this->getParameter('map_height')
        ];
    }

    /**
     * @Route("/polygon/peter_street", name="polygon_peter_street")
     *
     * @throws NotFoundHttpException
     * @Template()
     */

    public function getPolygonPeterStreetAction(Request $request)
    {
        $street_points = $request->request->get('street_points');
        $points_check = $request->request->get('points_check') !== 'false' ? true : false;
        $street_name = $request->request->get('street_name');
        $object_filter = $request->request->get('object_filter');
        $street_socr = $request->request->get('street_socr') !== 'null' ? $request->request->get('street_socr') : false;

        $pp_ar = array();
        if ($street_points && $points_check) {
            $pp_ar = json_decode($street_points);
        }

        if (count($street_socr) === 1 && !strlen($street_socr[0])) {
            $street_socr = false;
        }

        $YMaps = $this->container->get('ymaps_service');

        $streets = $YMaps->peterFilter($street_name, $street_socr, $pp_ar);

        return array('streets' => $streets);
    }

    /**
     * @Route("/", name="homepage")
     *
     * @throws NotFoundHttpException
     * @Template()
     */

    public function indexAction()
    {
        $ymaps_key = $this->container->getParameter('ymaps_key');
        // var_dump($ymaps_key);exit;

        return array('ymaps_key' => $ymaps_key);
    }
}
