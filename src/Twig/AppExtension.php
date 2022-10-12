<?php

namespace App\Twig;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Grants the ability to inline webpack built CSS in emails
 */
class AppExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(
        readonly private string $publicDir,
        readonly private ContainerInterface $container
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntrypointLookupInterface::class,
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_css_source', [$this, 'getEncoreEntryCssSource']),
        ];
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        /** @var EntrypointLookupInterface $entryPointLookup */
        $entryPointLookup = $this->container->get(EntrypointLookupInterface::class);
        $entryPointLookup->reset();

        $files = $entryPointLookup->getCssFiles($entryName);
        $source = '';

        /** @var string $file */
        foreach ($files as $file) {
            $url = filter_var($file, FILTER_VALIDATE_URL) ?: sprintf('%s%s', $this->publicDir, $file);
            $source .= file_get_contents($url);
        }

        return $source;
    }
}
