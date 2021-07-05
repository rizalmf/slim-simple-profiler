<?php

namespace Simple\Profiler;

class Container
{
    private $darkMode;
    private $eloquentManager;
    private $doctrineStack;

    public function __construct() {
        // default mode
        $this->darkMode = true;
    }

    /**
     * @param boolean
     */
    public function setDarkMode($darkMode)
    {
        $this->darkMode = $darkMode;
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
    }

    /**
     * @return \Doctrine\DBAL\Logging\DebugStack
     */
    public function getDoctrineStack()
    {
        return $this->doctrineStack;
    }

    /**
     * @overide
     */
    public function toString()
    {
        return json_encode([
            'darkMode' => $this->getDarkMode(),
            'eloquent' => is_object($this->getEloquentManager()),
            'doctrine' => is_object($this->getDoctrineStack())
        ], JSON_PRETTY_PRINT);
    }
}