<?php


namespace Mojo\Template;


interface IEngine {
    /**
     * void setTemplateFolder(string $path)
     * 
     * Used to indicate the location on disk to search for files when rendering, the file
     * path used in templates should be relative to the "TemplateFolder" set in this function.
     * Mojo & Centro use this method to set the selected theme on each request.
     */
    public function setTemplateFolder($path);

    /**
     * void setCacheFolder(string $path)
     */
    public function setCacheFolder($path);


    public function setIgnoreCache($state=TRUE);

    public function assign($name, $val);
    public function assignByRef($name, &$var);

    public function renderString($template_source, $var_bag=NULL, $return_result=FALSE);
    public function render($template_file, $var_bag=NULL, $return_result=FALSE);
}