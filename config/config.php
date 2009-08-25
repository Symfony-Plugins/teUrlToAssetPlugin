<?php
if(sfConfig::get("app_teUrlToAssetPlugin_enabled", true)) 
{
  $this -> dispatcher -> connect('template.filter_parameters', array("teUrlToAssetMapper", 'filterTemplateParameters'));
}