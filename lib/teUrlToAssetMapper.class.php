<?php 

class teUrlToAssetMapper implements ArrayAccess
{	
  protected $instance;
  protected $basedir;
  protected $url;
  protected $cache = array();
  protected $default_type;

  public function __construct($basedir, $url, $baseurl = "uploads", $default_type = "jpg")
  {
    $this -> basedir = $basedir;
    $this -> url = $url;
    $this -> baseurl = $baseurl;
    $this -> default_type = $default_type;
  }

  public static function filterTemplateParameters(sfEvent $event, $parameters)
  {
    $class  = __CLASS__;

    $basedir  = sfConfig::get("sf_web_dir");
    $url      = sfContext::getInstance() -> getRequest() -> getPathInfo();
    $baseurl  = sfConfig::get("app_teUrlToAssetPlugin_dir", "uploads");

    $template_name = sfConfig::get("app_teUrlToAssetPlugin_template_name", "assets");
    $parameters[$template_name] = new $class($basedir, $url, $baseurl);
    return $parameters;
  }

  /**
   *  required for ArrayAccess interface
   */
  public function offsetExists($name)
  {
    return is_dir($this -> basedir.DIRECTORY_SEPARATOR.$this -> baseurl.DIRECTORY_SEPARATOR.$name);
  }

  /**
   *  required for ArrayAccess interface
   */
  public function offsetGet($name)
  {
    $type = (strpos($name, ".")) ? substr($name, strrpos($name, ".") + 1) : $this -> default_type;
    $name = (strpos($name, ".")) ? substr($name, 0, strpos($name, ".")) : $name;
    
    if(!isset($this -> cache[$type][$name]))
    {
      $this -> cache[$type][$name] = null; //assume the worst
      if($paths = $this -> getPathsFromUrl($this -> url))
      {
        $paths[] = "../".$name;
        foreach($paths as $url)
        {
          $url = DIRECTORY_SEPARATOR.$this -> baseurl.DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR.$url.".".$type;
          $path = $this -> basedir.$url;
          if(file_exists($path))
          {
            $this -> cache[$type][$name] = str_replace(DIRECTORY_SEPARATOR, "/", $url);
            break;
          }
        }
      }
    }

    return $this -> cache[$type][$name];
  }

  /**
   *  required for ArrayAccess interface
   */
  public function offsetSet($name, $value)
  {
    throw new LogicException('Cannot update helper fields.');
  }

  /**
   *  required for ArrayAccess interface
   */
  public function offsetUnset($offset)
  {
    throw new LogicException('Cannot update helper fields.');
  }

  protected static function getPathsFromUrl($url)
  {
    $bits = explode("/", trim($url, "/"));
    $previous_bits = array();
    foreach($bits as $bit)
    {
        if(count($previous_bits) > 0)
        {
            $urls[] = implode("/", $previous_bits)."/".$bit;
        }
        else
        {
            $urls[] = $bit;
        }
        $previous_bits[] = $bit;
    }
    return isset($urls) ? array_reverse($urls) : null;
  }
}