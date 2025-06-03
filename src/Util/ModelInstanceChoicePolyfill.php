<?php

namespace HeimrichHannot\FieldValueCopierBundle\Util;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DC_Table;
use Contao\System;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ModelInstanceChoicePolyfill
{
    public const TITLE_FIELDS = [
        'name',
        'title',
        'headline',
    ];

    /**
     * Context data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Current file cache.
     */
    protected ?FilesystemAdapter $cache = null;

    /**
     * Current cache key name.
     */
    protected string $cacheKey;

    /**
     * Current context.
     */
    protected $context;

    protected ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    public function getChoices($context = [])
    {
        if (!$context) {
            $context = [];
        }

        $this->setContext($context);

        $choices = $this->collect();

        return $choices;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCachedChoices($context = [])
    {
        if (null === $context) {
            $context = [];
        }

        if (\is_array($context) && !isset($context['locale']) && ($request = System::getContainer()->get('request_stack')->getCurrentRequest())) {
            $context['locale'] = $request->getLocale();
        }

        $this->setContext($context);

        // disable cache while in debug mode or backend
        if (true === System::getContainer()->getParameter('kernel.debug')
            || System::getContainer()->get(Utils::class)->container()->isBackend()) {
            return $this->getChoices($this->getContext());
        }

        $this->cacheKey = 'choice.' . preg_replace('#Choice$#', '', (new \ReflectionClass($this))->getShortName());

        // add unique identifier based on context
        if (null !== $this->getContext() && false !== ($json = json_encode($this->getContext(), \JSON_FORCE_OBJECT))) {
            $this->cacheKey .= '.' . sha1($json);
        }

        if (!$this->cache) {
            $this->cache = new FilesystemAdapter('', 0, System::getContainer()->get('kernel')->getCacheDir());
        }

        $cache = $this->cache->getItem($this->cacheKey);

        if (!$cache->isHit() || empty($cache->get())) {
            $choices = $this->getChoices($this->getContext());

            if (!\is_array($choices)) {
                $choices = [];
            }

            // TODO: clear cache on delegated field save_callback
            $cache->expiresAfter(\DateInterval::createFromDateString('4 hour'));
            $cache->set($choices);

            $this->cache->save($cache);
        }

        return $cache->get();
    }

    protected function collect(): array
    {
        $context = $this->getContext();
        $choices = [];

        $utils = System::getContainer()->get(Utils::class);
        $instances = $utils->model()
            ->findModelInstancesBy(
                $context['dataContainer'],
                $context['columns'] ?? [],
                $context['values'] ?? null,
                is_array($context['options'] ?? null) ? $context['options'] : []
            );

        if (null === $instances) {
            return $choices;
        }

        while ($instances->next()) {
            $labelPattern = $context['labelPattern'] ?? null;

            if (!$labelPattern) {
                $labelPattern = 'ID %id%';

                switch ($context['dataContainer']) {
                    case 'tl_member':
                        $labelPattern = '%firstname% %lastname% (ID %id%)';

                        break;

                    default:
                        foreach (static::TITLE_FIELDS as $titleField) {
                            if (isset($GLOBALS['TL_DCA'][$context['dataContainer']]['fields'][$titleField])) {
                                $labelPattern = '%' . $titleField . '% (ID %id%)';

                                break;
                            }
                        }

                        break;
                }
            }

            $skipFormatting = $context['skipFormatting'] ?? false;

            if (!$skipFormatting) {
                if (method_exists($utils, 'formatter')) {
                    // note: originally new \HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils(...);
                    $dc = new DC_Table($context['dataContainer']);
                    $dc->id = $instances->id;
                    /* @phpstan-ignore property.notFound */
                    $dc->activeRecord = $instances->current();
                    $label = preg_replace_callback(
                        '@%([^%]+)%@i',
                        fn ($matches) => System::getContainer()->get(Utils::class)->formatter()
                            ->formatDcaFieldValue($dc, $matches[1], $instances->{$matches[1]}),
                        $labelPattern
                    );
                } else {
                    // fallback for utils v2
                    Controller::loadDataContainer($context['dataContainer']);
                    Controller::loadLanguageFile($context['dataContainer']);
                    $label = preg_replace_callback(
                        '@%([^%]+)%@i',
                        fn($matches) => $GLOBALS['TL_DCA'][$context['dataContainer']]['fields'][$matches[1]]['label'][0] ?? $matches[1],
                        $labelPattern
                    );
                }

                
            } else {
                $label = preg_replace_callback(
                    '@%([^%]+)%@i',
                    fn ($matches) => $instances->{$matches[1]},
                    $labelPattern
                );
            }

            $label = $context['label']
                ?? $utils->dca()->executeCallback($context['label_callback'] ?? null, [$label, $instances->row(), $context])
                ?? $label;

            $choices[$instances->id] = $label;
        }

        if (!isset($context['skipSorting']) || !$context['skipSorting']) {
            natcasesort($choices);
        }

        return $choices;
    }
}
