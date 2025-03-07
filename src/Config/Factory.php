<?php

namespace Vasoft\BxBackupTools\Config;

class Factory
{
    public function load(string $environment = 'local', string $path = ''): Container
    {

         // @codeCoverageIgnoreStart
        if (empty($path)) {
            $path = $this->getRoot();
        }
        // @codeCoverageIgnoreEnd
        $settings = $this->loadFile($path . 'config.php');
        $settingsLocal = $this->loadFile($path . 'config.' . $environment . '.php');
        if (!empty($settingsLocal)) {
            $settings = array_replace_recursive($settings, $settingsLocal);
        }
        return new Container($settings);
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getRoot(): string
    {
        $files = get_included_files();
        return preg_replace('/[^\/]+$/', '', $files[0]);
    }

    private function loadFile(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }
        $config = [];
        require $path;
        return $config;
    }
}

