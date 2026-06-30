<?php

namespace App\Component\Grid\Ux;

use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('admin:resource_form', 'Admin/Crud/Create/_live_form.html.twig', route: 'live_component_admin')]
final class ResourceFormComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public function __construct(
        private readonly DenormalizerInterface $denormalizer,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    #[LiveProp]
    public string $resourceClass;

    #[LiveProp(hydrateWith: 'hydrate', dehydrateWith: 'dehydrate', fieldName: 'data')]
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

    /**
     * @return FormInterface<mixed>
     */
    protected function instantiateForm(): FormInterface
    {
        $this->showErrorMessage = false;

        if (!is_a($this->formType, FormTypeInterface::class, true)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid form type class.', $this->formType));
        }

        /** @var class-string<FormTypeInterface<mixed>> $formType */
        $formType = $this->formType;

        return $this->createForm($formType, $this->resource);
    }

    public function dehydrate(?ResourceInterface $resource): mixed
    {
        if (null === $resource) {
            return null;
        }

        try {
            return $this->normalizer->normalize($resource, 'json');
        } catch (ExceptionInterface $exception) {
            throw new \LogicException(sprintf(
                'The normalizer was used to dehydrate/normalize the "%s" property on your "%s" live component, but it failed: %s',
                'resource',
                \get_class($this),
                $exception->getMessage(),
            ), 0, $exception);
        }
    }

    public function hydrate(mixed $value): ?ResourceInterface
    {
        try {
            $resource = $this->denormalizer->denormalize(
                $value,
                $this->resourceClass,
                'json',
            );
        } catch (ExceptionInterface $exception) {
            $json = json_encode($value);
            $message = sprintf(
                'The normalizer was used to hydrate/denormalize the "%s" property on your "%s" live component, but it failed: %s',
                'resource',
                \get_class($this),
                $exception->getMessage(),
            );

            if ($json !== false && \strlen($json) < 1000) {
                $message .= sprintf(' The data sent from the frontend was: %s', $json);
            }

            throw new \LogicException($message, 0, $exception);
        }

        if ($resource !== null && !$resource instanceof ResourceInterface) {
            throw new \UnexpectedValueException(sprintf(
                'Denormalizer returned "%s" instead of "%s".',
                get_debug_type($resource),
                $this->resourceClass,
            ));
        }

        return $resource;
    }
}
