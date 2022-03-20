<?php

namespace App\Component\Grid\Ux;

use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveComponentHydrator;

#[AsLiveComponent('admin:resource_form', 'Admin/Crud/Create/_live_form.html.twig', route: 'live_component_admin')]
final class ResourceFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public function __construct(
        private readonly NormalizerInterface | DenormalizerInterface $normalizer,
    ) {
    }

    #[LiveProp]
    public string $resourceClass;

    #[LiveProp(hydrateWith: 'hydrate', fieldName: 'data')]
    public ?ResourceInterface $resource = null;

    #[LiveProp]
    public string $action;

    #[LiveProp]
    public string $backUrl;

    #[LiveProp]
    public string $header;

    #[LiveProp]
    public string $formType;

    #[LiveProp]
    public bool $showErrorMessage = true;

    protected function instantiateForm(): FormInterface
    {
        $this->showErrorMessage = false;

        return $this->createForm($this->formType, $this->resource);
    }

    public function hydrate($value)
    {
        try {
            return $this->normalizer->denormalize(
                $value,
                $this->resourceClass,
                'json',
                [LiveComponentHydrator::LIVE_CONTEXT => true],
            );
        } catch (ExceptionInterface $exception) {
            $json = json_encode($value);
            $message = sprintf(
                'The normalizer was used to hydrate/denormalize the "%s" property on your "%s" live component, but it failed: %s',
                'resource',
                \get_class($this),
                $exception->getMessage(),
            );

            // unless the data is gigantic, include it in the error to help
            if (\strlen($json) < 1000) {
                $message .= sprintf(' The data sent from the frontend was: %s', $json);
            }

            throw new \LogicException($message, 0, $exception);
        }
    }
}
