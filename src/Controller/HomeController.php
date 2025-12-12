<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\Controller;

use MyDigitalEnvironment\MyDigitalEnvironmentBundle\BundleExtension\MyDigitalEnvironmentApplicationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly Security $security,
    )
    {
    }


    /**
     * So, using a resource locator (@, check name?) work.
     * I doubt however that this is the intended way of calling templates from inside a bundle
     *
     * Main issue is that this :
     *      $this->render('example.html.twig')
     * Can in theory and in practice call upon a template that is either not present or outside the bundle
     *
     * For example :
     * - Our bundle has a template named `example.html.twig`
     * - The app that require/import My Digital Environment has a template named `example.html.twig`
     * - From the inside of the bundle we call `$this->render('example.html.twig')`
     * - The template that will be rendered will be the one inside the app
     *
     * Of the few bundles that I've seen using routing inside their bundles :
     *      - they don't use AbstractController
     *      - they use a custom template manager ?
     *
     * Cause seem to be that twig is targeting the app template folder
     * (`Unable to find template "openent.html.twig" (looked into: /app/templates)`)
     *
     * I either will need to configure twig in a specific manner to restrict it to our desired template folder when inside the bundle or keep using the resource locator.
     * Custom configuration or parent that then modify twig ?
     *
     * Not a priority right now.
     *
     * Leaning toward the resource locator.
     */
    // todo : search for how would mention/call a template from inside a bundle route + controller
    //  possible lead here ?: https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/issues/557#issuecomment-380346703
    //  can we change twig.yaml and would it apply for only inside the bundle ?

    public function landing(Request $request, TranslatorInterface $translator): Response
    {
        if (($this->security->getUser()) !== null) {
            return $this->home($request, $translator); // todo : setting to change method (may be a security risk with rogue bundles?)
        }
        return $this->redirectToRoute('my_digital_environment_user_login');
    }



    #[IsGranted('IS_AUTHENTICATED')]
    public function home(Request $request, TranslatorInterface $translator): Response
    {
        // todo: is it used ?
        dump($request->getLocale());
        dump($request->getDefaultLocale());
        $applications = [];
        foreach ($this->getParameter('kernel.bundles') as $bundle) {
            $isExtension = in_array(MyDigitalEnvironmentApplicationInterface::class, class_implements($bundle));
            if (!$isExtension) {
                continue;
            }

            $applications[] = [
                'route_id' => $bundle::getApplicationRouteId(),
                'description' => $bundle::getApplicationDescription(),
            ];
        }
        return $this->render('@MyDigitalEnvironment/home.html.twig', [
            'applications' => $applications,
        ]);
    }
}
