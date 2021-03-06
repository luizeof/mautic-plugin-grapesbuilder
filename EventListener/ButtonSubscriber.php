<?php
namespace MauticPlugin\MauticGrapesbuilderBundle\EventListener;

use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Mautic\CoreBundle\Event\CustomButtonEvent;
use Mautic\CoreBundle\Templating\Helper\ButtonHelper;
use Mautic\PageBundle\Entity\Page;
use Mautic\PluginBundle\Helper\IntegrationHelper;

class ButtonSubscriber extends CommonSubscriber
{
    protected $integrationHelper;

    /**
     * ButtonSubscriber constructor.
     *
     * @param IntegrationHelper $helper
     */
    public function __construct(IntegrationHelper $integrationHelper)
    {
        $this->integrationHelper = $integrationHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_BUTTONS => ['injectViewButtons', 0],
        ];
    }

    /**
     * @param CustomButtonEvent $event
     */
    public function injectViewButtons(CustomButtonEvent $event)
    {
        if ($event->checkRouteContext('mautic_grapesbuilder_index')) {
            $event->addButton(
                [
                    'attr' => [
                        'class' => 'btn btn-default btn-save btn-copy',
                        'id' => 'btn-grapesbuilder-save',
                    ],
                    'btnText' => $this->translator->trans('mautic.plugin.grapesbuilder.save'),
                    'iconClass' => 'fa fa-save',
                    'priority' => 255,
                ],
                ButtonHelper::LOCATION_TOOLBAR_ACTIONS
            );
            return;
        }

        if ($item = $event->getItem()) {
            $this->logger->info($event->getItem());
            if ($item instanceof Page) {
                /*$addBtn = [
                'attr' => [
                'href' => $this->router->generate(
                'mautic_grapesbuilder_index',
                ['objectType' => 'page', 'objectId' => $event->getItem()->getId()]
                ),
                ],
                'btnText' => $this->translator->trans('mautic.plugin.grapesbuilder.open'),
                'iconClass' => 'fa fa-star',
                'primary' => true,
                'priority' => 1,
                ];*/

                $href = $this->router->generate(
                    'mautic_grapesbuilder_index',
                    ['objectType' => 'page', 'callView' => 'normal', 'objectId' => $event->getItem()->getId()]);
                $this->addBtn($event, $this->translator->trans('mautic.plugin.grapesbuilder.open'), $href);
            } else if ($item instanceof Email) {
                $this->addBtn($event, $this->translator->trans('mautic.plugin.grapesbuilder.open'), $this->router->generate(
                    'mautic_grapesbuilder_index',
                    ['objectType' => 'email', 'callView' => 'mjml', 'objectId' => $event->getItem()->getId()]));
                $this->addBtn($event, $this->translator->trans('mautic.plugin.grapesbuilder.open'), $this->router->generate(
                    'mautic_grapesbuilder_index',
                    ['objectType' => 'email', 'callView' => 'html', 'objectId' => $event->getItem()->getId()]), 2);
            }
        }
    }

    private function addBtn(CustomButtonEvent $event, $btnText, $href, $priority = 1)
    {
        $addBtn = [
            'attr' => [
                'href' => $href,
            ],
            'btnText' => $btnText,
            'iconClass' => 'fa fa-cube',
            'primary' => true,
            'priority' => $priority,
        ];

        // Inject a button into the page actions for the specified route
        $event
            ->addButton(
                $addBtn,
                ButtonHelper::LOCATION_LIST_ACTIONS
            );
    }
}
