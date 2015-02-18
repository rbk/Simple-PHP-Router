<?php
class Router {
    private static $routes = array(self::PRIORITY_HIGH => array(), 
                                   self::PRIORITY_NORMAL => array(), 
                                   self::PRIORITY_LOW => array());
    private static $method;
    private static $source;
    private static $pattern;
    private static $ctrl;
    private static $scanned = FALSE;
    
    const ROUTE_PCRE = 0;
    const ROUTE_STATIC = 1;
    const REDIRECT_PCRE = 2;
    const REDIRECT_STATIC = 3;
    const PRIORITY_HIGH = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_LOW = 2;
    
    private static function scan($force = FALSE)
    {
        $found = FALSE;
        if (!self::$scanned || $force)
        {
            foreach (self::$routes as $priority => $routes) 
            {
                if ($found)
                {
                    break;
                }
                foreach ($routes as $route)
                {
                    if ($found)
                    {
                        break;
                    }
                    unset($ctrl, $redirect);
                    list($pattern, $replacement, $method, $source) = $route;
                    switch ($method)
                    {
                        case self::ROUTE_STATIC:
                            if (URI_PATH === $pattern)
                            {
                                $ctrl = $replacement;
                            }
                        break;
                        case self::ROUTE_PCRE:
                            if (preg_match($pattern, URI_PATH))
                            {
                                $ctrl = preg_replace($pattern, $replacement, URI_PATH);
                            }
                        break;
                        case self::REDIRECT_STATIC:
                            if (URI_PATH === $pattern)
                            {
                                $redirect = $replacement;
                            }
                        break;
                        case self::REDIRECT_PCRE:
                            if (preg_match($pattern, URI_PATH))
                            {
                                $redirect = preg_replace($pattern, $replacement, URI_PATH);
                            }
                        break;
                    }
                    if (isset($ctrl) || isset($redirect))
                    {
                        if (isset($ctrl) && is_readable($ctrl))
                        {
                            self::$pattern = $pattern;
                            self::$ctrl = $ctrl;
                            self::$method = $method;
                            self::$source = $source;
                            $found = TRUE;
                        }
                    }
                }
            }
            if (!self::$ctrl && !isset($redirect) && substr(URI_PATH, -1) !== '/')
            {
                $redirect = URI_PATH . '/';
                if (strlen(URI_PARAM))
                {
                    $redirect .= '?'.URI_PARAM;
                }
            }
            if (isset($redirect))
            {
                header('Location: '.$redirect);
                exit;
            }
            self::$scanned = !$force;
        }
    }

    public static function controller($scan = FALSE)
    {
        self::scan($scan);
        return self::$ctrl;
    }

    public static function pattern($scan = FALSE)
    {
        self::scan($scan);
        return self::$pattern;
    }

    public static function method($scan = FALSE)
    {
        self::scan($scan);
        return self::$method;
    }
    
    public static function source($scan = FALSE)
    {
        self::scan($scan);
        return self::$source;
    }

    public static function add($pattern, $ctrl, $route = Router::ROUTE_STATIC, $priority = Router::PRIORITY_NORMAL, $source = NULL)
    {
        self::$routes[$priority][] = array($pattern, $ctrl, $route, $source);
    }
}



?>
