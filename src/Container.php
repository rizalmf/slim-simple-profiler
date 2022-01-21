<?php

namespace Simple\Profiler;

class Container
{
    const HTTP_FORMAT = 'text/html';
    const JSON_FORMAT = 'application/json';

    private $darkMode;
    private $eloquentManager;
    private $doctrineStack;
    private $responseFormat;

    public function __construct() {
        // default mode
        $this->darkMode = true;
        $this->responseFormat = Container::HTTP_FORMAT;
    }

    /**
     * @param boolean
     */
    public function setDarkMode($darkMode)
    {
        $this->darkMode = $darkMode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDarkMode()
    {
        return $this->darkMode;
    }

    /**
     * @param \Illuminate\Database\Capsule\Manager $eloquentManager
     */
    public function setEloquentManager($eloquentManager)
    {
        $this->eloquentManager = $eloquentManager;
        return $this;
    }

    /**
     * @return \Illuminate\Database\Capsule\Manager
     */
    public function getEloquentManager()
    {
        return $this->eloquentManager;
    }

    /**
     * @param \Doctrine\DBAL\Logging\DebugStack $debugStack
     */
    public function setDoctrineStack($debugStack)
    {
        $this->doctrineStack = $debugStack;
        return $this;
    }

    /**
     * @return \Doctrine\DBAL\Logging\DebugStack
     */
    public function getDoctrineStack()
    {
        return $this->doctrineStack;
    }

    /**
     * @param string $responseFormat
     */
    public function setResponseFormat($responseFormat)
    {
        $this->responseFormat = $responseFormat;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseFormat()
    {
        return $this->responseFormat;
    }

    /**
     * @overide
     */
    public function toString()
    {
        return json_encode([
            'darkMode' => $this->getDarkMode(),
            'eloquent' => is_object($this->getEloquentManager()),
            'doctrine' => is_object($this->getDoctrineStack()),
            'responseFormat' => $this->getResponseFormat()
        ], JSON_PRETTY_PRINT);
    }
}
