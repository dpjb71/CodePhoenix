<?php
namespace Phoenix\Web\UI;

/**
 * Description of tgrid
 *
 * @author david
 */
abstract class TPluginRenderer extends \Phoenix\MVC\TPartialController
{
    use \Phoenix\Web\UI\THtmlPattern;
    use \Phoenix\Data\UI\TDataBinder;
    
    protected static $templateFilename;

    public function renderHtml()
    {
//        if($this->data !== null) {
//            $this->columns = $this->data['cols'];
//            $this->rows = $this->data['rows'];
//            $elements = $this->data['elements'];
//            $this->templates = $this->data['templates'];
//        } else {
            $this->getElements();
            $elements = $this->elements[$this->getPattern()];
            //\Phoenix\Log\TLog::dump('PATTERN ELEMENTS', $elements);

            $id = $this->getParent()->getId();

            $result = array();
            $c = count($elements);
            for($i = 0; $i < $c; $i++) {
                array_push($result, ['opening' => $elements[$i]->getOpening(), 'closing' => $elements[$i]->getClosing()]);
            }

            $json = json_encode($result);
            $elementsFilename = TMP_DIR . DIRECTORY_SEPARATOR . $id . '_elements.json';
            file_put_contents($elementsFilename, $json);
//        }
        
        
        
        $pluginClass = '\Phoenix\Web\UI\Plugin\T' . ucfirst($this->getPattern());
        $plugin = new $pluginClass($this);
        $plugin->setId($this->getId());
        $plugin->setCols($this->columns);
        $plugin->setRows($this->rows);
        $plugin->setData($this->data);
        $plugin->setPivot($this->pivot);
        $plugin->setTemplates($this->templates);
        $plugin->setElements($elements);
        $this->innerHtml = $plugin->render();
    }

}