<?php
namespace Phoenix\Web\UI;

use Phoenix\Core\TObject;

class TControl extends TCustomControl
{
    use \Phoenix\Web\TWebObject;

    protected $model = NULL;
    private $_theme = '';
    protected $innerHtml = '';
    protected $isDreclared = false;

    public function __construct(TObject $parent)
    {
        
        $this->setParent($parent);
        
        $this->setViewName();
        $this->setNamespace();
        $this->setFilenames();
        
        $this->className = $this->getType();
        $this->viewName = lcfirst($this->className);
        
        $include = \Phoenix\TAutoloader::includeModelByName($this->viewName);
        $modelClass = $include['type'];
        \Phoenix\Log\TLog::debug('TCONTROL MODEL OBJECT : ' . print_r($modelClass, true));
        $this->model = new $modelClass();        

        $this->request = $parent->getRequest();
        $this->response = $parent->getResponse();        
    }

    public function getModel()
    {
        return $this->model;
    }
       
    public function getInnerHtml()
    {
        return $this->innerHtml;
    }

    public function createObjects() {}
    
    public function declareObjects() {}

    public function displayHtml() {}
    
    public function getViewHtml()
    {
        ob_start();
        if(!$this->isDreclared) {
            //$this->createObjects();
            $this->declareObjects();
        }
        $this->displayHtml();
        $html = ob_get_clean();
        $this->unload();

        $this->response->setData('view', $html);
    }   
    
    public function render()
    {
        $this->createObjects();
        $this->init();
        $this->declareObjects();
        $this->isDreclared = true;
        $this->displayHtml();
        $this->renderHtml();
        $this->unload();
    }
    
    public function perform()
    {
        $this->createObjects();
        $this->init();
        if($this->request->isAJAX()) {
            $actionName = $this->actionName;
            $this->$actionName();
            if($this->request->isPartialView()) {
                $this->getViewHtml();
            }
            $this->response->sendData();
        } else {
            $this->load();
            $this->declareObjects();
            $this->displayHtml();
            $this->unload();
        }        
    }
    
    public function __destruct()
    {
        unset($this->model);
    }

}