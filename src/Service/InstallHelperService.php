<?php

namespace MelisInstaller\Service;

use Laminas\Json\Json;
use PDO;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Adapter as DbAdapter;
use Laminas\Db\Sql\Ddl;

class InstallHelperService extends AbstractService
{
    const CONN_OK = 200;
    const CONN_MOVED_PERMANENTLY = 301;
    const CONN_TEMP_REDIRECT = 307;
    CONST CONN_AUTHORIZED = 401;
    CONST CONN_FORBIDDEN = 403;
    const CONN_BAD_REQUEST = 404;
    CONST CONN_INTERAL_ERROR = 500;
    CONST CONN_BAD_GATEWAY = 502;
    CONST CONN_SERVICE_UNAVAILABLE = 503;
    const CHMOD_775 = 0775;

    const MODULES_ONLY = 2;
    const SITE_ONLY    = 1;

    protected $extensions;
    protected $odbAdapter;
    protected $importStatus = array();
    protected $importTableName = array();

    /**
     * Sets what extensions should be required when checking pre-installe extensions
     * @param array $ext
     */
    public function setRequiredExtensions(array $ext)
    {
        $this->extensions = $ext;
    }

    /**
     * Returns the required extensions
     * @return array
     */
    public function getRequiredExtensions()
    {
        return (array) $this->extensions;
    }

    /**
     * Returns all loaded PHP Extensions
     */
    public function getPhpExtensions()
    {
        return get_loaded_extensions();
    }

    /**
     * Check if the desired extension is loaded
     * @param array $ext
     * @return array
     */
    public function isExtensionsExists($ext)
    {
        $status = 0;
        if(!empty($ext)) {
            if(extension_loaded($ext)) {
                $status = 1;
            }
        }

        return $status;
    }

    /**
     * Checks if the URL exists
     * @param String $domain
     * @return boolean
     */
    public function isDomainExists($domain)
    {
        $host = gethostbyname($domain);

        if($host != $domain) {
            return true;
        }

        return false;
    }

    /**
     * Returns the current URL of the page
     * @return string
     */
    public function getDomain()
    {
        $uri = $this->getServiceManager()->get('Application')->getMvcEvent()->getRequest()->getUri();
        return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    }


    /**
     * Checks if your MySql is working and if your database is existing
     * @param string $host
     * @param string $db
     * @param string $user
     * @param string $pass
     * @return Json
     */
    public function checkMysqlConnection($host, $db, $user, $pass)
    {
        $results                        = array();
        $isConnected                    = 0;
        $isDatabaseExists               = 0;
        $isDatabaseCollationNameValid   = 0;
        $isPassCorrect                  = 1;

        if($this->isDomainExists($host)) {
            $isConnected = 1;

            try {
                $dbAdapter = new DbAdapter(array(
                    'driver' =>  'Pdo',
                    'dsn'   =>   'mysql:dbname=INFORMATION_SCHEMA;host='.$host,
                    'username' => $user,
                    'password' => $pass,
                    'driver_options' => array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
                    ),
                ));

                $sql = new Sql($dbAdapter);
                $select = $sql->select();
                $select->from('SCHEMATA');
                $select->where(array('SCHEMA_NAME' => $db));

                $statement = $sql->prepareStatementForSqlObject($select);
                $result = $statement->execute();

                $schema = $result->current();
                if(!empty($schema)) {

                    $isDatabaseExists = 1;

                    if (!empty($schema['DEFAULT_COLLATION_NAME']) && $schema['DEFAULT_COLLATION_NAME'] === 'utf8_general_ci') {
                        $isDatabaseCollationNameValid = 1;
                    }
                }
            }catch(\Exception $e) {
                $isPassCorrect = 0;
            }
        }

        $results = array(
            'isConnected' => $isConnected,
            'isDatabaseExists' => $isDatabaseExists,
            'isMysqlPasswordCorrect' => $isPassCorrect,
            'isDatabaseCollationNameValid' => $isDatabaseCollationNameValid,
        );


        return $results;
    }

    /**
     * Set's the DB Adapter
     * @param String $config
     */
    public function setDbAdapter($config)
    {
        if(is_array($config)) {
            $this->odbAdapter = new DbAdapter(array_merge(array(
                'driver' => 'Pdo_Mysql',
                'driver_options' => array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
                )
            ), $config));

            $config = new \Laminas\Config\Config($config, true);
            $writer = new \Laminas\Config\Writer\PhpArray();
            $conf = $writer->toString($config);
        }
    }

    /**
     * Returns the set DB Adapter
     */
    protected function getDbAdapter()
    {
        return $this->odbAdapter;
    }

    /**
     * Executes a raw SQL query
     * @param String $query
     * @return NULL|\Laminas\Db\Adapter\Driver\StatementInterface|\Laminas\Db\ResultSet\Zend\Db\ResultSet
     */
    public function executeRawQuery($query)
    {
        $resultSet = null;
        if($this->odbAdapter) {
            if(!empty($query)) {
                $resultSet = $this->getDbAdapter()->query($query, DbAdapter::QUERY_MODE_EXECUTE);

            }
        }


        return $resultSet;
    }

    /**
     * Checks if the table exists or not
     * @param String $tableName
     * @return boolean
     */
    public function isDbTableExists($tableName)
    {
        $status = false;
        $resultSet = array();

        $query = $this->executeRawQuery("SHOW TABLES LIKE '".trim($tableName)."';");
        if($resultSet)
            $resultSet = $query->toArray();



        if(!empty($resultSet)) {
            $status = true;

        }

        return $status;
    }

    /**
     * Drop Table
     * @param unknown $tableName
     */
    public function dropDbTable($tableName)
    {
        $drop = new Ddl\DropTable(trim($tableName));
        $sql  = new Sql($this->getDbAdapter());
        $this->getDbAdapter()->query($sql->getSqlStringForSqlObject($drop), DbAdapter::QUERY_MODE_EXECUTE);
    }

    /**
     * Imports the specified file in the MySql server
     * @param String $path
     * @param array $files (optional)
     * @return boolean
     */
    public function importSql($path, $files = array('setup_structure.sql'))
    {
        $status = false;
        $fImport = null;
        if(file_exists($path)) {
            foreach($files as $file) {
                if(file_exists($path.$file)) {
                    $fImport = file_get_contents($path.$file);
                    $this->executeRawQuery($fImport);
                    $this->importTableName = array_merge($this->importTableName, $this->getSqlFileTables($path.$file));
                }
            }
        }

        return $status;
    }

    /**
     * Returns all the table names that will be imported
     * @param String $path
     * @return array
     */
    public function getSqlFileTables($path)
    {
        $tables = array();

        if(file_exists($path)) {
            $file = fopen($path, 'r');
            $textToFind = 'CREATE TABLE IF NOT EXISTS ';
            $found = array();
            $tables = array();
            while(!feof($file)) {
                $output = fgets($file);
                $pos = strrpos($output, $textToFind);
                if(is_int($pos)) {
                    $found[] = $output;
                }
            }

            foreach($found as $table) {
                $startPos = strpos($table, '`');
                $lastPos = strrpos($table, '`');
                $tableName = substr($table, $startPos, $lastPos);
                $tableName = preg_replace('/\(|\)/', '', $tableName);
                $tables[] = trim(str_replace('`', '', $tableName));
            }
        }

        return $tables;
    }

    public function getImportedTables()
    {
        return (array) $this->importTableName;
    }

    public function evalSqlImportStatus()
    {
        if(!empty($this->importTableName)) {

            foreach($this->importTableName as $tableName) {
                if($this->isDbTableExists($tableName)) {
                    $this->importStatus = array_merge_recursive($this->importStatus, array('installed' => $tableName));
                }
                else {
                    $this->importStatus = array_merge_recursive($this->importStatus, array('failed' => $tableName));
                }
            }

        }

    }

    /**
     * Returns all the imported table status
     */
    public function getSqlImportStatus()
    {
        return (array) $this->importStatus;
    }

    /**
     * REturns memory limit, execution time, and maximum upload size variables
     * @return array
     */
    public function checkEnvironmentVariables()
    {
        $settingsValues = array();
        $iniSettings = array(
            'memory_limit', 'max_execution_time', 'upload_max_filesize'
        );

        foreach($iniSettings as $setting) {
            $settingsValues[$setting] = ini_get($setting);
        }

        return $settingsValues;
    }

    /**
     * Returns the current platform
     * @return Json
     */
    public function getMelisPlatform()
    {
        return getenv('MELIS_PLATFORM');
    }

    /**
     * Returns the HTTP/1.1 status of the path/url
     * @param unknown $path
     * @return Json
     */
    public function getUrlStatus($path, $domain = '')
    {

        $status = null;
        $url    = null;
        $uri    = empty($domain) ? $this->getDomain() : '';

        $hasSlash = strpos($path, '/');
        // add forward slash
        if($hasSlash === false) {
            $path = '/' . $path;
        }

        $url = $uri . $path;
        if($url) {
            stream_context_set_default(
                array(
                    'http' => array(
                        'method' => 'HEAD'
                    )
                )
            );

            $url    = get_headers($url, 1);
            $status = @explode(' ',$url[0]);

            if($status)
                $status = (int) $status[1];
        }


        return array(
            'status' => $status
        );
    }

    /**
     * Changes the file permission
     * @param String $path
     * @param int $mode
     * @return Json
     */
    public function filePermission($path, $mode = self::CHMOD_775)
    {
        $results = array();
        $success = 0;
        if(file_exists($path)) {
            if(!is_writable($path))
                chmod($path, $mode);

            if(!is_readable($path))
                chmod($path, $mode);

            if(is_readable($path) && is_writable($path))
                $status = 1;
        }

        $results = array(
            'path' => $path,
            'mode' => $mode,
            'success' => $success
        );

        return Json::encode($results);
    }

    /**
     * Returns the status of the provided directories
     * @param String $dir
     * @return array0
     */
    public function isDirWritable($dir)
    {
        $dirStatus = 0;

        if(is_writable($dir)) {
            $dirStatus = 1;
        }

        return $dirStatus;
    }

    /**
     * Returns all the sub-folders in the provided path
     * @param String $dir
     * @param array $excludeSubFolders
     * @return array
     */
    public function getDir($dir, $excludeSubFolders = array())
    {
        $directories = array();
        if(file_exists($dir)) {
            $excludeDir = array_merge(array('.', '..', '.gitignore'), $excludeSubFolders);
            $directory  = array_diff(scandir($dir), $excludeDir);

            foreach($directory as $d) {
                if(is_dir($dir.'/'.$d)) {
                    $directories[] = $d;
                }
            }

        }

        return $directories;
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       int      $permissions New folder creation permissions
     * @return      bool     Returns true on success, false on failure
     */
    function xcopy($source, $dest, $permissions = self::CHMOD_775)
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->xcopy("$source/$entry", "$dest/$entry", $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }

    public function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }

        }

        return rmdir($dir);
    }

    /**
     * Replaces the old file from the specified path
     * @param string $old
     * @param string $new
     * @param Array  $content (vsprintf content)
     */
    public function replaceFile($old, $new, array $content)
    {
        $oldFileContent = file_get_contents($old);
        file_put_contents($new, vsprintf($oldFileContent, $content));
        unlink($old);
    }

    public function getAvailableModules()
    {
        $moduleExceptions = array('MelisFront', 'MelisEngine', 'MelisCore', 'MelisSites', 'MelisModuleConfig', 'MelisInstaller', 'MelisCms', 'MelisAssetManager');
        $modules = $this->getModuleSvc()->getAllModules();

        $finalListModules = array();
        foreach ($modules as $module)
        {
            if (!in_array($module, $moduleExceptions))
                $finalListModules[] = $module;
        }

        return $finalListModules;
    }

    public function getRequiredModules()
    {
        return array('MelisEngine', 'MelisCore');
    }

    public function isModuleExists($module)
    {
        $status = false;
        $modulesSvc = $this->getServiceManager()->get('MelisInstallerModulesService');
        $pathModule = $modulesSvc->getModulePath($module);

        if(file_exists($pathModule)) {
            $status = true;
        }

        return $status;
    }

    public function replaceFileTextContent($fileName, $outputFileName, $lookupText, $replaceText)
    {
        $file = @file_get_contents($fileName);
        $file = str_replace($lookupText, $replaceText, $file);
        @file_put_contents($outputFileName, $file);
    }

    public function getPackages($type)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $config             = $this->getServiceManager()->get('MelisInstallerConfig');
        $marketplace        = $config->getItem('melis_installer/datas')['marketplace_url'];

        $packages           = [];
        $requestJsonUrl     = $marketplace.'/melis-packagist/get-packages/page/1/search//item_per_page/0/order/asc/order_by//status/2/group//siteonly/'.$type.'/bundle/';

        $config             = $this->getServiceManager()->get('MelisInstallerConfig');
        $moduleExceptions   = $config->getItem('melis_installer/datas/module_exceptions');
        $serverPackages     = null;
        try {

            $serverPackages = @file_get_contents($requestJsonUrl);
            $serverPackages = Json::decode($serverPackages, Json::TYPE_ARRAY);

        }catch(\Exception $e) {
            echo $e->getMessage();
            $serverPackages = array();
        }


        if ($serverPackages) {
            $moduleExceptions = array_map(function ($a) {
                return strtolower(trim($a));
            }, $moduleExceptions);

            if (isset($serverPackages['packages']) && $serverPackages['packages']) {
                foreach ($serverPackages['packages'] as $package) {
                    /**
                     * If type is 1 (for site)
                     * we must check if the site is active
                     */
                    if($type == 1){
                        if ($package['packageIsActive']) {
                            if (!in_array(strtolower(trim($package['packageModuleName'])), $moduleExceptions))
                                $packages[] = $package;
                        }
                    }else{
                        if (!in_array(strtolower(trim($package['packageModuleName'])), $moduleExceptions))
                            $packages[] = $package;
                    }
                }
            }

        }


        return array(
            'packages'  => $packages,
        );
    }

    public function getPackagistMelisModules()
    {
        return $this->getPackages(self::MODULES_ONLY);
    }

    public function getPackagistMelisSites()
    {
        return $this->getPackages(self::SITE_ONLY);
    }

}
