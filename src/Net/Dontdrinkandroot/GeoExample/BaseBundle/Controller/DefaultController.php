<?php

namespace Net\Dontdrinkandroot\GeoExample\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Net\Dontdrinkandroot\GeoExample\BaseBundle\Service\GeoService;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('searchString', 'text')
            ->add(
                'actions',
                'form_actions',
                array(
                    'buttons' => array(
                        'search' => array('type' => 'submit', 'options' => array('label' => 'Search')),
                    )
                )
            )
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $searchString = $form->get('searchString')->getData();
            $cities = $this->getGeoService()->search($searchString);

            return $this->render(
                'DdrGeoExampleBaseBundle:Default:index.html.twig',
                ['form' => $form->createView(), 'cities' => $cities]
            );
        }

        return $this->render('DdrGeoExampleBaseBundle:Default:index.html.twig', ['form' => $form->createView()]);
    }

    public function cityDetailAction(Request $request, $id)
    {
        $city = $this->getGeoService()->getCity($id);
        $nearbyCities = $this->getGeoService()->findNearbyCities($city, 50);

        return $this->render('DdrGeoExampleBaseBundle:Default:cityDetail.html.twig', ['city' => $city, 'nearbyCities' => $nearbyCities]);
    }

    /**
     * @return GeoService
     */
    protected function getGeoService()
    {
        return $this->get('ddr_geo_example.service.geo');
    }
}
