<?php

declare(strict_types=1);

namespace Ray\Query;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;
use Ray\Query\Annotation\GuzzleConfig;

class WebQueryModule extends AbstractModule
{
    /**
     * @var array
     */
    private $guzzleConfig;
    /**
     * @var array
     */
    private $webQueryConfig;

    public function __construct(array $webQueryConfig, array $guzzleConfig, AbstractModule $module = null)
    {
        $this->guzzleConfig = $guzzleConfig;
        $this->webQueryConfig = $webQueryConfig;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->configureClient();
        foreach ($this->webQueryConfig as $name => $value) {
            list($method, $uri) = $value;
            $this->configureWebQuery($name, $method, $uri);
        }
    }

    private function configureWebQuery(string $name, string $method, string $uri)
    {
        $prefixedName = 'wq-' . $name;
        $this
            ->bind(QueryInterface::class)
            ->annotatedWith($name)
            ->toConstructor(
                WebQuery::class,
                [
                    'method' => $prefixedName . '-method',
                    'uri' => $prefixedName . '-uri',
                ]
            );
        $this
            ->bind()
            ->annotatedWith($name)
            ->toConstructor(
                WebQuery::class,
                [
                    'method' => $prefixedName . '-method',
                    'uri' => $prefixedName . '-uri',
                ]
            );
        $this
            ->bind(RowInterface::class)
            ->annotatedWith($name)
            ->toConstructor(
                WebQuery::class,
                [
                    'method' => $prefixedName . '-method',
                    'uri' => $prefixedName . '-uri',
                ]
            );
        $this
            ->bind(RowListInterface::class)
            ->annotatedWith($name)
            ->toConstructor(
                WebQuery::class,
                [
                    'method' => $prefixedName . '-method',
                    'uri' => $prefixedName . '-uri',
                ]
            );
        $this->bind()->annotatedWith($prefixedName . '-method')->toInstance($method);
        $this->bind()->annotatedWith($prefixedName . '-uri')->toInstance($uri);
    }

    /**
     * {@inheritdoc}
     */
    private function configureClient()
    {
        $this
            ->bind(ClientInterface::class)
            ->toConstructor(
                Client::class,
                ['config' => GuzzleConfig::class]
            )->in(Scope::SINGLETON);
        $this->bind()->annotatedWith(GuzzleConfig::class)->toInstance($this->guzzleConfig);
    }
}
