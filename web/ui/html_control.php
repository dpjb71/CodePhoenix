<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Phoenix\Web\UI;
/**
 * Description of html_control
 *
 * @author David
 */
trait THtmlControl 
{
    //put your code here
    protected $name = '';
    protected $image = '';
    protected $content = '';
    protected $enabled = true;
    protected $event = '';

    public function getEnabled()
    {
        return $this->enabled;
    }
    public function setEnabled($value)
    {
        //$value = (is_string($value)) ? ((strtolower($value) == 'false') ? 0 : 1) : 1;
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $this->enabled = $value;
    }

    public function getName()
    {
        return $this->name;
    }
    public function setName($value)
    {
        $this->name = $value;
    }

    public function getImage()
    {
        return $this->image;
    }
    public function setImage($value)
    {
        $this->image = $value;
    }

    public function getContent()
    {
        return $this->content;
    }
    public function setContent($value)
    {
        $this->content = $value;
        if($this->content[0] == '@') {
            $templateName = str_replace(PREHTML_EXTENSION, '', substr($this->content,1));
            $templateName = 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $templateName . DIRECTORY_SEPARATOR . $templateName . PREHTML_EXTENSION;

            if(file_exists($templateName)) {
                $contents = file_get_contents($templateName, FILE_USE_INCLUDE_PATH);
                $this->content = $contents;
            }
        }
    }
    
    public function getEvent()
    {
        return $this->event;
    }
    public function setEvent($value)
    {
        $this->event = $value;
    }

    public function getProperties()
    {
        return [
            'image' => $this->image
          , 'name' => $this->name
          , 'event' => $this->event
          , 'content' => $this->content
          , 'enabled' => $this->enabled
        ];
    }
    
    public function getControl() 
    {
        return (object) $this->getProperties();
    }
    
    public function sleep()
    {
        $object = serialize($this->getControl());
    }
}