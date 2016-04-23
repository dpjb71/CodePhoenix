<?php
namespace Phoenix\Web\UI\Widget\Plugin;

/**
 * Description of adatacolumn
 *
 * @author david
 */
class TPluginChild extends \Phoenix\Core\TObject
{
    use \Phoenix\Web\UI\THtmlControl;
    
    private $_value;

    public function getValue()
    {
        return $this->_value;
    }
    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function data()
    {
        return $this->_value;
    }

}
