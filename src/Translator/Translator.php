<?php

namespace Wandi\EasyAdminPlusBundle\Translator;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Translation\Dumper\FileDumper;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class Translator
{
    private $locales;
    private $directories;
    private $excludedDomains = [];
    private $cacheDir = null;

    /**
     * Translator constructor.
     *
     * @param array  $config   EasyAdminPlus config
     * @param string $cacheDir kernel.cache_dir
     *
     * @throws \Exception
     */
    public function __construct(array $config, string $cacheDir)
    {
        $this->parseConfig($config);
        $this->cacheDir = $cacheDir;

        if (!is_dir($this->cacheDir)) {
            throw new \Exception(sprintf("The %s cache directory doesn't exist.", $this->cacheDir));
        }
    }

    /**
     * Parse EasyAdminPlus config.
     *
     * @param array $config
     */
    private function parseConfig(array $config)
    {
        $this->locales = $config['translator']['locales'];
        $this->directories = $config['translator']['paths'];
        $this->excludedDomains = $config['translator']['excluded_domains'];
    }

    /**
     * List base translations files.
     *
     * @return array files
     *
     * @throws \Exception
     */
    public function getFiles(): array
    {
        $files = [];

        foreach ($this->directories as $directory) {
            if (!is_dir($directory)) {
                throw new \Exception(sprintf("The %s directory doesn't exist.", $directory));
            }
        }

        foreach (Finder::create()->in($this->directories)->depth('< 1')->files() as $file) {
            // skip mismatch files
            if (!preg_match('/^([^\.]+)\.([^\.]+)\.([^\.]+)(?<!~)$/', basename($file), $match) || !in_array($match[2], $this->locales)) {
                continue;
            }

            // skip excluded domains
            if (in_array($match[1], $this->excludedDomains)) {
                continue;
            }

            $files[$match[1]][$match[2]][] = $file;
        }

        uksort($files, 'strcasecmp');

        return $files;
    }

    /**
     * Load catalogs from files and extract base translations.
     *
     * @param array $files list of files
     *
     * @return array translations
     *
     * @throws \Exception
     */
    public function getTranslations(array $files): array
    {
        $translations = [];

        foreach ($files as $domain => $locales) {
            foreach ($locales as $locale => $files) {
                foreach ($files as $file) {
                    /** @var SplFileInfo $file */
                    if (class_exists($loaderPath = 'Symfony\\Component\\Translation\\Loader\\'.$this->getLoader(strtolower($file->getExtension())))) {
                        /** @var LoaderInterface $loader */
                        // use the correct loader for file extension and extract all the translation keys from catalog
                        $loader = new $loaderPath();
                        $catalog = $loader->load($file, $locale, $domain);
                        if (!empty($catalog->all())) {
                            $translations[$domain][$locale][$file->getPath().'/'.$file->getRelativePathname()] = $catalog->all()[$domain];
                        }
                    } else {
                        throw new \Exception(sprintf('No loader found for %s extension', $file->getExtension()));
                    }
                }
            }
        }

        return $translations;
    }

    /**
     * Process and extend base translations.
     *
     * @param array $translations base translations
     * @param array $dictionaries dictionaries of different files to manage
     *
     * @return array $translations extended translations
     */
    public function prepareTranslations(array $translations, array &$dictionaries): array
    {
        $translations = $this->prepareTranslationsFiles($translations, $dictionaries);
        $translations = $this->prepareTranslationsKeys($translations, $dictionaries);

        return $translations;
    }

    /**
     * Add missing files in others locales.
     *
     * @param array $translations all translations
     * @param array $dictionaries dictionaries of different files to manage
     *
     * @return array $translations extended translations
     */
    private function prepareTranslationsFiles(array $translations, array &$dictionaries): array
    {
        foreach ($translations as $domain => $langs) {
            foreach ($langs as $lang => $files) {
                foreach ($files as $file => $sentences) {
                    // skip mismatch files
                    if (!preg_match('/^(.*)\/([^\.]+)\.([^\.]+)\.([^\.]+)$/', $file, $match)) {
                        continue;
                    }

                    // save all different files
                    if (!array_key_exists($domain, $dictionaries)) {
                        $dictionaries[$domain] = [];
                    }
                    if (!array_key_exists($dictionary = $match[1].'/'.$match[2].'.'.$match[4], $dictionaries[$domain])) {
                        $dictionaries[$domain][$dictionary] = [];
                    }

                    // clone non-existing files in other locales
                    foreach ($this->locales as $locale) {
                        if ($locale !== $lang) {
                            if (!array_key_exists($locale, $translations[$domain])) {
                                $translations[$domain][$locale] = [];
                            }
                            if (!array_key_exists($fileName = $match[1].'/'.$match[2].'.'.$locale.'.'.$match[4], $translations[$domain][$locale])) {
                                $translations[$domain][$locale][$fileName] = [];
                            }
                        }
                    }
                }
            }
        }

        return $translations;
    }

    /**
     * Add missing keys in others locales.
     *
     * @param array $translations all translations
     * @param array $dictionaries dictionaries of different files to manage
     *
     * @return array $translations extended translations
     */
    private function prepareTranslationsKeys(array $translations, array &$dictionaries): array
    {
        // clone non existing translation keys in other locales
        foreach ($dictionaries as $domain => $files) {
            foreach ($files as $file => $sentences) {
                // skip mismatch files
                if (!preg_match('/^(.*)\/([^\.]+)\.([^\.]+)$/', $file, $match)) {
                    continue;
                }

                // merge all different keys from all locales
                foreach ($this->locales as $locale) {
                    $dictionaries[$domain][$file] = array_merge($dictionaries[$domain][$file], array_keys($translations[$domain][$locale][$match[1].'/'.$match[2].'.'.$locale.'.'.$match[3]]));
                }
                $dictionaries[$domain][$file] = array_unique($dictionaries[$domain][$file]);

                // merge default keys with keys/values from the current locale
                $defaultKeys = array_combine($dictionaries[$domain][$file], array_fill(0, count($dictionaries[$domain][$file]), ''));
                foreach ($this->locales as $locale) {
                    $path = $match[1].'/'.$match[2].'.'.$locale.'.'.$match[3];
                    $translations[$domain][$locale][$path] = array_merge($defaultKeys, $translations[$domain][$locale][$path]);
                }
            }
        }

        return $translations;
    }

    /**
     * Prepare dictionaries for front-end.
     *
     * @param array $translations base translations
     * @param array $dictionaries dictionaries of different files to manage
     *
     * @return array $properDictionaries proper dictionaries
     */
    public function formatDictionaries(array $translations, array $dictionaries): array
    {
        $properDictionaries = [];

        foreach ($dictionaries as $domain => $files) {
            foreach ($files as $file => $sentences) {
                // skip mismatch files
                if (!preg_match('/^(.*)\/([^\.]+)\.([^\.]+)$/', $file, $match)) {
                    continue;
                }

                $properDictionaries[$domain][$file] = [];
                foreach ($this->locales as $locale) {
                    $properDictionaries[$domain][$file][$locale] = $translations[$domain][$locale][$match[1].'/'.$match[2].'.'.$locale.'.'.$match[3]];
                }
            }
        }

        return $properDictionaries;
    }

    /**
     * Write and save dictionaries on files.
     *
     * @param array  $dictionaries dictionaries to write
     * @param string $userLocale   the default user locale
     *
     * @return int $nbWrittenFiles number of written files
     *
     * @throws \Exception
     */
    public function writeDictionaries(array $dictionaries, string $userLocale): int
    {
        $nbWrittenFiles = 0;

        foreach ($dictionaries as $domain => $files) {
            foreach ($files as $file => $locales) {
                // skip mismatch files
                if (!preg_match('/^(.*)\/([^\.]+)\.([^\.]+)$/', $file, $match)) {
                    continue;
                }

                foreach ($locales as $locale => $sentences) {
                    $path = $match[1].'/'.$match[2].'.'.$locale.'.'.$match[3];

                    // create the catalog in the given locale
                    $catalog = new MessageCatalogue($locale, [$domain => $sentences]);

                    if (class_exists($dumperPath = 'Symfony\\Component\\Translation\\Dumper\\'.$this->getDumper(strtolower($match[3])))) {
                        // make backup if existing file
                        if (file_exists($path)) {
                            copy($path, $path.'~');
                        }

                        /** @var FileDumper $dumper */
                        // use the correct dumper for file extension and save all the translation keys from catalog
                        $dumper = new $dumperPath();

                        // calc the max depth of the array
                        uksort($sentences, function ($a, $b) {
                            return substr_count($a, '.') < substr_count($b, '.');
                        });
                        $maxDepth = (!empty($sentences)) ? (substr_count(array_keys($sentences)[0], '.') + 1) : 1;

                        // dump the file in the correct format
                        if (!file_put_contents($path, $dumper->formatCatalogue($catalog, $domain, [
                            'as_tree' => true,
                            'inline' => $maxDepth,
                            'default_locale' => $userLocale,
                        ]))) {
                            throw new \Exception(sprintf('The %s directory is not writable.', $match[1]));
                        } else {
                            ++$nbWrittenFiles;
                        }
                    }
                }
            }
        }

        return $nbWrittenFiles;
    }

    /**
     * Get the managed locales.
     *
     * @return array $locales user locales
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Clear translation cache dir.
     */
    public function clearTranslationsCache()
    {
        array_map('unlink', glob($this->cacheDir.'/translations/*'));
    }

    /**
     * Get the correct loader for given extension.
     *
     * @param string $extension
     *
     * @return string class name's loader
     */
    private function getLoader(string $extension): string
    {
        $loaders = [
            'csv' => 'CsvFileLoader',
            'ini' => 'IniFileLoader',
            'mo' => 'MoFileLoader',
            'po' => 'PoFileLoader',
            'php' => 'PhpFileLoader',
            'ts' => 'QtFileLoader',
            'xlf' => 'XliffFileLoader',
            'yaml' => 'YamlFileLoader',
            'yml' => 'YamlFileLoader',
            'json' => 'JsonFileLoader',
        ];

        return $loaders[$extension];
    }

    /**
     * Get the correct dumper for given extension.
     *
     * @param string $extension
     *
     * @return string class name's dumper
     */
    private function getDumper(string $extension): string
    {
        $dumpers = [
            'csv' => 'CsvFileDumper',
            'ini' => 'IniFileDumper',
            'mo' => 'MoFileDumper',
            'po' => 'PoFileDumper',
            'php' => 'PhpFileDumper',
            'ts' => 'QtFileDumper',
            'xlf' => 'XliffFileDumper',
            'yaml' => 'YamlFileDumper',
            'yml' => 'YamlFileDumper',
            'json' => 'JsonFileDumper',
        ];

        return $dumpers[$extension];
    }
}
